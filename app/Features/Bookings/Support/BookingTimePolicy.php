<?php

namespace App\Features\Bookings\Support;

use App\Models\ClassEnrollment;
use Carbon\CarbonImmutable;

class BookingTimePolicy
{
    public const MIN_BOOKING_DAYS_BEFORE = 1;

    public const CANCEL_CUTOFF_HOURS = 3;

    public static function earliestBookingDate(): CarbonImmutable
    {
        return CarbonImmutable::today()->addDays(self::MIN_BOOKING_DAYS_BEFORE);
    }

    public static function canBookForDate(CarbonImmutable $sessionDate): bool
    {
        return $sessionDate->startOfDay()->greaterThanOrEqualTo(self::earliestBookingDate());
    }

    public static function bookingDateMessage(): string
    {
        return 'Booking kelas minimal 1 hari sebelum jadwal.';
    }

    public static function canCancel(ClassEnrollment $enrollment): bool
    {
        if (! $enrollment->session_date) {
            return false;
        }

        return CarbonImmutable::now()->lt(self::cancelCutoffAt($enrollment));
    }

    public static function cancelCutoffAt(ClassEnrollment $enrollment): CarbonImmutable
    {
        $sessionDate = CarbonImmutable::parse($enrollment->session_date->toDateString());
        $startTime = substr((string) $enrollment->schedule?->start_time, 0, 5) ?: '00:00';

        return CarbonImmutable::parse($sessionDate->toDateString().' '.$startTime)
            ->subHours(self::CANCEL_CUTOFF_HOURS);
    }

    public static function cancelCutoffMessage(): string
    {
        return 'Booking kelas hanya bisa dibatalkan paling lambat 3 jam sebelum kelas dimulai.';
    }
}
