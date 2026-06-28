<?php

namespace App\Notifications\Payments;

use App\Models\Payment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Number;

class PaymentRejectedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public readonly Payment $payment) {}

    /** @return array<int, string> */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $payment = $this->payment->loadMissing(['payable.package', 'invoice']);

        return (new MailMessage)
            ->subject('Pembayaran Ditolak - '.$payment->payment_code)
            ->markdown('mail.payments.rejected', [
                'user' => $notifiable,
                'payment' => $payment,
                'serviceName' => $payment->payable?->package?->name ?? 'Layanan Platinum Gym',
                'amount' => Number::currency((float) $payment->amount, 'IDR', 'id'),
                'reason' => $payment->rejected_reason ?: $payment->failure_reason ?: 'Bukti pembayaran belum sesuai.',
                'actionUrl' => route('member.transactions'),
            ]);
    }
}
