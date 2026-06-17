<?php

namespace App\Features\Payments\Actions;

use App\Features\Payments\Contracts\PaymentGateway;
use App\Models\Payment;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class SyncMidtransPaymentStatusAction
{
    public function __construct(
        private readonly PaymentGateway $gateway,
        private readonly FulfillPaidPaymentAction $fulfillPaidPayment,
    ) {}

    /**
     * Pull the latest transaction status from Midtrans Core API and apply the
     * same fulfillment/cancellation logic as the webhook handler. Returns the
     * refreshed Payment, possibly with a new status.
     *
     * Idempotent: paid payments return immediately without remote calls.
     */
    public function handle(Payment $payment): Payment
    {
        if ($payment->status === 'paid') {
            return $payment;
        }

        if (blank($payment->midtrans_order_id)) {
            return $payment;
        }

        // Skip the remote call entirely when Midtrans credentials are absent
        // (e.g. test bootstrap, fresh local install). This keeps the action
        // free of side effects and noisy logs in those scenarios.
        if (blank(config('services.midtrans.server_key'))) {
            return $payment;
        }

        try {
            $payload = $this->gateway->fetchTransactionStatus($payment);
        } catch (RuntimeException $exception) {
            report($exception);

            return $payment;
        }

        if (! is_array($payload)) {
            return $payment;
        }

        $transactionStatus = (string) Arr::get($payload, 'transaction_status');
        $fraudStatus = (string) Arr::get($payload, 'fraud_status', '');

        if ($transactionStatus === '') {
            return $payment;
        }

        if ($this->isPaid($transactionStatus, $fraudStatus)) {
            $payment->forceFill([
                'midtrans_transaction_id' => Arr::get($payload, 'transaction_id', $payment->midtrans_transaction_id),
                'midtrans_payment_type' => Arr::get($payload, 'payment_type', $payment->midtrans_payment_type),
                'midtrans_raw_response' => $payload,
            ])->save();

            return $this->fulfillPaidPayment->handle($payment);
        }

        $status = $this->mapStatus($transactionStatus, $fraudStatus);

        return DB::transaction(function () use ($payment, $payload, $status): Payment {
            $payment = Payment::query()->lockForUpdate()->findOrFail($payment->id);

            if ($payment->status === 'paid') {
                return $payment;
            }

            // Don't downgrade waiting_confirmation/waiting_payment to identical state with a noisy update;
            // but always store the raw response so the audit trail is complete.
            $shouldUpdate = $payment->status !== $status
                || filled($payload);

            if (! $shouldUpdate) {
                return $payment;
            }

            $payment->forceFill([
                'status' => $status,
                'midtrans_transaction_id' => Arr::get($payload, 'transaction_id', $payment->midtrans_transaction_id),
                'midtrans_payment_type' => Arr::get($payload, 'payment_type', $payment->midtrans_payment_type),
                'midtrans_raw_response' => $payload,
                'failure_reason' => in_array($status, ['failed', 'expired', 'cancelled'], true)
                    ? (string) Arr::get($payload, 'status_message', 'Pembayaran tidak berhasil.')
                    : $payment->failure_reason,
            ])->save();

            if (in_array($status, ['failed', 'expired', 'cancelled'], true)) {
                $this->cancelPayable($payment);
                $payment->invoice?->forceFill(['status' => $status])->save();
            }

            return $payment->refresh();
        });
    }

    private function isPaid(string $transactionStatus, string $fraudStatus): bool
    {
        if ($transactionStatus === 'settlement') {
            return true;
        }

        return $transactionStatus === 'capture' && ! in_array($fraudStatus, ['challenge', 'deny'], true);
    }

    private function mapStatus(string $transactionStatus, string $fraudStatus): string
    {
        return match ($transactionStatus) {
            'pending' => 'waiting_payment',
            'expire' => 'expired',
            'cancel' => 'cancelled',
            'deny', 'failure' => 'failed',
            'capture' => $fraudStatus === 'challenge' ? 'waiting_confirmation' : 'failed',
            default => 'waiting_payment',
        };
    }

    private function cancelPayable(Payment $payment): void
    {
        $payable = $payment->payable;

        if (! $payable || $payable->status === 'active') {
            return;
        }

        $payable->forceFill(['status' => 'cancelled'])->save();
    }
}
