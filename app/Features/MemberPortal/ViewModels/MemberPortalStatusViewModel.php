<?php

namespace App\Features\MemberPortal\ViewModels;

use App\Features\Bookings\Support\BookingTimePolicy;
use App\Models\ClassEnrollment;
use App\Models\ClassSchedule;
use App\Models\Package;
use App\Models\Payment;
use Illuminate\Notifications\DatabaseNotification;

class MemberPortalStatusViewModel
{
    /**
     * @return array{label: string, class: string, can_pay: bool}
     */
    public static function payment(Payment $payment): array
    {
        $status = (string) $payment->status;

        return [
            'label' => self::paymentLabel($status),
            'class' => self::paymentClass($status),
            'can_pay' => in_array($status, ['waiting_payment', 'pending', 'unpaid'], true) && filled($payment->midtrans_redirect_url),
        ];
    }

    /**
     * @return array{label: string, class: string, can_cancel: bool}
     */
    public static function booking(ClassEnrollment $enrollment): array
    {
        $status = (string) $enrollment->status;

        return [
            'label' => self::bookingLabel($status),
            'class' => self::bookingClass($status),
            'can_cancel' => ! in_array($status, ['cancelled', 'canceled'], true) && BookingTimePolicy::canCancel($enrollment),
        ];
    }

    /**
     * @return array{label: string, class: string}
     */
    public static function notification(DatabaseNotification $notification): array
    {
        return is_null($notification->read_at)
            ? ['label' => 'Baru', 'class' => 'member-status-warning']
            : ['label' => 'Dibaca', 'class' => 'member-status-neutral'];
    }

    /**
     * @return array{kind_label: string, is_membership: bool, type_label: string, requires_trainer: bool, price: float, promo_price: float|null, has_promo: bool, display_price: float, duration_label: string|null, bonus_label: string|null, effective_duration_days: int|null}
     */
    public static function package(Package $package): array
    {
        $price = (float) ($package->price ?? 0);
        $promoPrice = filled($package->promo_price) ? (float) $package->promo_price : null;
        $hasPromo = $promoPrice !== null && $promoPrice > 0 && $promoPrice < $price;

        return [
            'kind_label' => self::packageKindLabel((string) $package->package_kind),
            'is_membership' => $package->package_kind === 'membership',
            'type_label' => $package->package_kind === 'membership' ? 'Membership' : 'Sesi',
            'requires_trainer' => self::packageRequiresTrainer((string) $package->package_kind, (string) $package->type),
            'trainer_specialization' => self::trainerSpecializationFor((string) $package->package_kind, (string) $package->type),
            'price' => $price,
            'promo_price' => $promoPrice,
            'has_promo' => $hasPromo,
            'display_price' => $hasPromo ? (float) $promoPrice : $price,
            'duration_label' => $package->durationMarketingLabel(),
            'bonus_label' => $package->durationBonusLabel(),
            'effective_duration_days' => $package->effectiveDurationDays(),
        ];
    }

    /**
     * @return array{access_label: string, access_class: string, capacity_left: int, capacity_full: bool, day_label: string, is_paid: bool, is_included: bool, is_session_based: bool, member_price: float|null, non_member_price: float|null, promo_price: float|null, display_price: float|null, has_promo: bool, button_label: string, can_book: bool, disabled_reason: string|null, alert_message: string|null, cta_label: string|null, cta_url: string|null}
     */
    public static function schedule(ClassSchedule $schedule): array
    {
        $gymClass = $schedule->gymClass;
        $accessType = (string) $gymClass?->access_type;
        $capacity = (int) ($schedule->capacity ?? $gymClass?->capacity ?? 0);
        $bookedCount = (int) ($schedule->booked_count ?? 0);
        $capacityLeft = max($capacity - $bookedCount, 0);
        $capacityFull = $capacity > 0 && $capacityLeft === 0;

        $memberPrice = filled($gymClass?->member_price) ? (float) $gymClass->member_price : null;
        $nonMemberPrice = filled($gymClass?->non_member_price) ? (float) $gymClass->non_member_price : null;
        $promoPrice = filled($gymClass?->promo_price) ? (float) $gymClass->promo_price : null;
        $isPaid = $accessType === 'paid';

        $displayPrice = null;
        if ($isPaid) {
            $displayPrice = $promoPrice ?? $memberPrice ?? $nonMemberPrice;
        }

        $hasPromo = $isPaid && $promoPrice !== null
            && $memberPrice !== null
            && $promoPrice < $memberPrice;

        $buttonLabel = match (true) {
            $capacityFull => 'Kuota Habis',
            $isPaid && $displayPrice !== null && $displayPrice > 0 => 'Booking & Bayar Rp '.number_format((float) $displayPrice, 0, ',', '.'),
            default => 'Booking Kelas',
        };

        return [
            'access_label' => self::classAccessLabel($accessType),
            'access_class' => $accessType === 'paid' ? 'member-status-warning' : 'member-status-neutral',
            'capacity_left' => $capacityLeft,
            'capacity_full' => $capacityFull,
            'day_label' => self::dayLabel((int) $schedule->day_of_week),
            'is_paid' => $isPaid,
            'is_included' => $accessType === 'included',
            'is_session_based' => $accessType === 'session_based',
            'member_price' => $memberPrice,
            'non_member_price' => $nonMemberPrice,
            'promo_price' => $promoPrice,
            'display_price' => $displayPrice,
            'has_promo' => $hasPromo,
            'button_label' => $buttonLabel,
            'can_book' => true,
            'disabled_reason' => null,
            'alert_message' => null,
            'cta_label' => null,
            'cta_url' => null,
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function paymentStatusOptions(): array
    {
        return [
            'waiting_payment' => 'Menunggu Bayar',
            'waiting_confirmation' => 'Menunggu Konfirmasi',
            'paid' => 'Lunas',
            'rejected' => 'Ditolak',
            'failed' => 'Gagal',
            'expired' => 'Kedaluwarsa',
            'cancelled' => 'Dibatalkan',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function bookingStatusOptions(): array
    {
        return [
            'booked' => 'Terdaftar',
            'confirmed' => 'Terkonfirmasi',
            'pending_payment' => 'Menunggu Bayar',
            'cancelled' => 'Dibatalkan',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function notificationStatusOptions(): array
    {
        return [
            'baru' => 'Baru',
            'dibaca' => 'Dibaca',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function packageKindOptions(): array
    {
        return [
            'membership' => 'Membership',
            'personal_trainer' => 'Personal Trainer',
            'muaythai' => 'Muaythai',
            'session' => 'Paket Sesi',
        ];
    }

    /**
     * @return array<int, string>
     */
    public static function dayOptions(): array
    {
        return [
            1 => 'Senin',
            2 => 'Selasa',
            3 => 'Rabu',
            4 => 'Kamis',
            5 => 'Jumat',
            6 => 'Sabtu',
            7 => 'Minggu',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function classAccessOptions(): array
    {
        return [
            'included' => 'Membership',
            'session_based' => 'Paket Sesi',
        ];
    }

    public static function invoiceLabel(?string $status): ?string
    {
        if (blank($status)) {
            return null;
        }

        return match ($status) {
            'issued' => 'Diterbitkan',
            'paid' => 'Lunas',
            'rejected' => 'Ditolak',
            'cancelled', 'canceled' => 'Dibatalkan',
            default => str($status)->headline()->toString(),
        };
    }

    private static function paymentLabel(string $status): string
    {
        return match ($status) {
            'waiting_payment', 'pending', 'unpaid' => 'Menunggu Bayar',
            'waiting_confirmation' => 'Menunggu Konfirmasi',
            'paid' => 'Lunas',
            'rejected' => 'Ditolak',
            'failed' => 'Gagal',
            'expired' => 'Kedaluwarsa',
            'cancelled', 'canceled' => 'Dibatalkan',
            default => str($status)->headline()->toString(),
        };
    }

    private static function paymentClass(string $status): string
    {
        return match ($status) {
            'paid' => 'member-status-success',
            'waiting_payment', 'pending', 'unpaid', 'waiting_confirmation' => 'member-status-warning',
            'rejected', 'failed', 'expired', 'cancelled', 'canceled' => 'member-status-danger',
            default => 'member-status-neutral',
        };
    }

    private static function bookingLabel(string $status): string
    {
        return match ($status) {
            'booked', 'active', 'confirmed' => 'Terdaftar',
            'pending_payment' => 'Menunggu Bayar',
            'cancelled', 'canceled' => 'Dibatalkan',
            default => str($status)->headline()->toString(),
        };
    }

    private static function bookingClass(string $status): string
    {
        return match ($status) {
            'booked', 'active', 'confirmed' => 'member-status-success',
            'pending_payment' => 'member-status-warning',
            'cancelled', 'canceled' => 'member-status-danger',
            default => 'member-status-neutral',
        };
    }

    private static function packageKindLabel(string $kind): string
    {
        return self::packageKindOptions()[$kind] ?? str($kind)->replace('_', ' ')->headline()->toString();
    }

    public static function packageRequiresTrainer(string $packageKind, string $packageType): bool
    {
        return $packageKind !== 'membership' && in_array($packageType, ['pt', 'muaythai'], true);
    }

    public static function trainerSpecializationFor(string $packageKind, string $packageType): ?string
    {
        if ($packageKind === 'membership') {
            return null;
        }

        return match ($packageType) {
            'pt' => 'Personal Trainer',
            'muaythai' => 'Muaythai',
            default => null,
        };
    }

    private static function classAccessLabel(string $accessType): string
    {
        return match ($accessType) {
            'included' => 'Membership',
            'session_based' => 'Paket Sesi',
            'paid' => 'Bayar Kelas',
            default => 'Kelas',
        };
    }

    private static function dayLabel(int $day): string
    {
        return self::dayOptions()[$day] ?? 'Jadwal';
    }
}
