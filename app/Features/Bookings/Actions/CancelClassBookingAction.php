<?php

namespace App\Features\Bookings\Actions;

use App\Models\ClassEnrollment;
use App\Notifications\MemberOperationalNotification;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class CancelClassBookingAction
{
    public function handle(ClassEnrollment $enrollment, string $reason = 'Dibatalkan oleh member.'): ClassEnrollment
    {
        return DB::transaction(function () use ($enrollment, $reason): ClassEnrollment {
            $enrollment = ClassEnrollment::query()->with(['member.user', 'schedule.gymClass'])->lockForUpdate()->findOrFail($enrollment->id);

            if (in_array($enrollment->status, ['cancelled', 'canceled'], true)) {
                return $enrollment;
            }

            if ($enrollment->session_date?->isPast()) {
                throw new RuntimeException('Booking kelas yang sudah lewat tidak dapat dibatalkan.');
            }

            if ($enrollment->attendance()->exists()) {
                throw new RuntimeException('Booking yang sudah memiliki kehadiran tidak dapat dibatalkan.');
            }

            $enrollment->forceFill([
                'status' => 'cancelled',
                'cancelled_at' => now(),
                'cancel_reason' => $reason,
            ])->save();

            $payment = $enrollment->payment ?? $enrollment->payments()->latest()->first();

            if ($payment && $payment->status !== 'paid') {
                $payment->forceFill([
                    'status' => 'cancelled',
                    'failure_reason' => 'Booking kelas dibatalkan.',
                ])->save();
                $payment->invoice?->forceFill(['status' => 'cancelled'])->save();
            }

            $enrollment->member?->user?->notify(new MemberOperationalNotification(
                'Booking Kelas Dibatalkan',
                'Booking '.$enrollment->schedule?->gymClass?->name.' sudah dibatalkan.',
                route('member.bookings'),
                'Lihat Riwayat',
            ));

            return $enrollment->refresh();
        });
    }
}
