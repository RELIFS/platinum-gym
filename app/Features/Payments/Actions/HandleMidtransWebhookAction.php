<?php

namespace App\Features\Payments\Actions;

use App\Models\Payment;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use RuntimeException;

class HandleMidtransWebhookAction
{
    public function __construct(private readonly FulfillPaidPaymentAction $fulfillPaidPayment) {}

    /**
     * @param  array<string, mixed>  $payload
     */
    public function handle(array $payload): Payment
    {
        $this->assertValidSignature($payload);

        $orderId = (string) Arr::get($payload, 'order_id');
        $payment = Payment::query()
            ->where('midtrans_order_id', $orderId)
            ->firstOrFail();

        $transactionStatus = (string) Arr::get($payload, 'transaction_status');
        $fraudStatus = (string) Arr::get($payload, 'fraud_status', '');

        if ($this->isPaid($transactionStatus, $fraudStatus)) {
            $payment->forceFill([
                'midtrans_transaction_id' => Arr::get($payload, 'transaction_id'),
                'midtrans_payment_type' => Arr::get($payload, 'payment_type'),
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

            $payment->forceFill([
                'status' => $status,
                'midtrans_transaction_id' => Arr::get($payload, 'transaction_id'),
                'midtrans_payment_type' => Arr::get($payload, 'payment_type'),
                'midtrans_raw_response' => $payload,
                'failure_reason' => in_array($status, ['failed', 'expired', 'cancelled'], true)
                    ? (string) Arr::get($payload, 'status_message', 'Pembayaran tidak berhasil.')
                    : null,
            ])->save();

            if (in_array($status, ['failed', 'expired', 'cancelled'], true)) {
                $this->cancelPayable($payment);
                $payment->invoice?->forceFill(['status' => $status])->save();
            }

            return $payment->refresh();
        });
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function assertValidSignature(array $payload): void
    {
        foreach (['order_id', 'status_code', 'gross_amount', 'signature_key'] as $key) {
            if (blank($payload[$key] ?? null)) {
                throw new InvalidArgumentException('Payload Midtrans tidak lengkap.');
            }
        }

        $serverKey = (string) config('services.midtrans.server_key');

        if (blank($serverKey)) {
            throw new RuntimeException('Konfigurasi Midtrans belum lengkap.');
        }

        $expected = hash('sha512', (string) $payload['order_id'].
            (string) $payload['status_code'].
            (string) $payload['gross_amount'].
            $serverKey);

        if (! hash_equals($expected, (string) $payload['signature_key'])) {
            throw new InvalidArgumentException('Signature Midtrans tidak valid.');
        }
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
