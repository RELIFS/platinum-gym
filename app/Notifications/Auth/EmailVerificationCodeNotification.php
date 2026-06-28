<?php

namespace App\Notifications\Auth;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Carbon;

class EmailVerificationCodeNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly string $code,
        public readonly string $maskedEmail,
        public readonly string $verificationUrl,
        public readonly Carbon $expiresAt,
    ) {}

    /** @return array<int, string> */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Kode Verifikasi Email Platinum Gym')
            ->markdown('mail.auth.email-verification-code', [
                'user' => $notifiable,
                'code' => $this->code,
                'maskedEmail' => $this->maskedEmail,
                'verificationUrl' => $this->verificationUrl,
                'expiresAt' => $this->expiresAt,
            ]);
    }
}
