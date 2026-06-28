<?php

namespace App\Notifications\Auth;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Carbon;

class MemberAccountInvitationNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly string $acceptUrl,
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
            ->subject('Aktivasi Akun Member Platinum Gym')
            ->markdown('mail.auth.member-account-invitation', [
                'user' => $notifiable,
                'acceptUrl' => $this->acceptUrl,
                'expiresAt' => $this->expiresAt,
            ]);
    }
}
