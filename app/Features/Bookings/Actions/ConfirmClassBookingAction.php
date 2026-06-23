<?php

namespace App\Features\Bookings\Actions;

use App\Models\ClassEnrollment;
use App\Notifications\MemberOperationalNotification;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class ConfirmClassBookingAction
{
    public function handle(ClassEnrollment $enrollment): ClassEnrollment
    {
        return DB::transaction(function () use ($enrollment): ClassEnrollment {
            $enrollment = ClassEnrollment::query()
                ->with(['member.user', 'schedule.gymClass'])
                ->lockForUpdate()
                ->findOrFail($enrollment->id);

            if ($enrollment->session_date?->isPast() && ! $enrollment->session_date?->isToday()) {
                throw new RuntimeException('Booking kelas yang sudah lewat tidak dapat dikonfirmasi.');
            }

            if ($enrollment->status === 'pending_payment') {
                throw new RuntimeException('Booking berbayar masih menunggu pembayaran. Konfirmasi dilakukan otomatis setelah pembayaran lunas.');
            }

            if (in_array($enrollment->status, ['cancelled', 'canceled'], true)) {
                throw new RuntimeException('Booking yang sudah dibatalkan tidak dapat dikonfirmasi.');
            }

            if ($enrollment->status === 'attended') {
                throw new RuntimeException('Booking yang sudah tercatat hadir tidak perlu dikonfirmasi ulang.');
            }

            if ($enrollment->status === 'confirmed') {
                return $enrollment;
            }

            if (! in_array($enrollment->status, ['booked', 'active'], true)) {
                throw new RuntimeException('Status booking belum dapat dikonfirmasi oleh admin.');
            }

            $enrollment->forceFill(['status' => 'confirmed'])->save();

            $enrollment->member?->user?->notify(new MemberOperationalNotification(
                'Booking Kelas Dikonfirmasi',
                'Booking '.$enrollment->schedule?->gymClass?->name.' sudah dikonfirmasi oleh admin.',
                route('member.bookings'),
                'Lihat Booking',
            ));

            return $enrollment->refresh();
        });
    }
}
