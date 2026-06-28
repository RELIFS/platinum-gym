<?php

namespace App\Notifications\Bookings;

use App\Models\ClassEnrollment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BookingCreatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public readonly ClassEnrollment $enrollment) {}

    /** @return array<int, string> */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Booking Kelas Berhasil')
            ->markdown('mail.bookings.status', [
                'user' => $notifiable,
                'enrollment' => $this->enrollment->loadMissing(['schedule.gymClass', 'schedule.trainer']),
                'headline' => 'Booking kelas Anda sudah tercatat.',
                'statusLabel' => 'Berhasil Dibooking',
                'actionUrl' => route('member.bookings'),
            ]);
    }
}
