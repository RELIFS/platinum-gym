<?php

namespace App\Features\Admin\ViewModels;

/**
 * Maps raw domain status strings to admin-facing labels and semantic pill
 * classes (admin-status-success/warning/danger/info/neutral defined in
 * resources/css/app.css). Keeps Blade free of repeated match() logic and gives
 * admin tables/cards a consistent, non-color-only status treatment.
 */
class AdminStatusViewModel
{
    /**
     * @return array{label: string, class: string}
     */
    public static function payment(?string $status): array
    {
        $status = (string) $status;

        return [
            'label' => match ($status) {
                'waiting_payment', 'pending', 'unpaid' => 'Menunggu Bayar',
                'waiting_confirmation' => 'Menunggu Konfirmasi',
                'paid' => 'Lunas',
                'rejected' => 'Ditolak',
                'failed' => 'Gagal',
                'expired' => 'Kedaluwarsa',
                'cancelled', 'canceled' => 'Dibatalkan',
                default => self::headline($status),
            },
            'class' => match ($status) {
                'paid' => 'admin-status-success',
                'waiting_payment', 'pending', 'unpaid', 'waiting_confirmation' => 'admin-status-warning',
                'rejected', 'failed', 'expired', 'cancelled', 'canceled' => 'admin-status-danger',
                default => 'admin-status-neutral',
            },
        ];
    }

    /**
     * @return array{label: string, class: string}
     */
    public static function member(?string $status): array
    {
        $status = (string) $status;

        return [
            'label' => match ($status) {
                'active' => 'Aktif',
                'inactive' => 'Nonaktif',
                'suspended' => 'Ditangguhkan',
                default => self::headline($status),
            },
            'class' => match ($status) {
                'active' => 'admin-status-success',
                'suspended' => 'admin-status-warning',
                'inactive' => 'admin-status-neutral',
                default => 'admin-status-neutral',
            },
        ];
    }

    /**
     * @return array{label: string, class: string}
     */
    public static function booking(?string $status): array
    {
        $status = (string) $status;

        return [
            'label' => match ($status) {
                'booked', 'active' => 'Terdaftar',
                'confirmed' => 'Terkonfirmasi',
                'attended' => 'Hadir',
                'pending_payment' => 'Menunggu Bayar',
                'cancelled', 'canceled' => 'Dibatalkan',
                default => self::headline($status),
            },
            'class' => match ($status) {
                'confirmed', 'attended' => 'admin-status-success',
                'booked', 'active' => 'admin-status-info',
                'pending_payment' => 'admin-status-warning',
                'cancelled', 'canceled' => 'admin-status-danger',
                default => 'admin-status-neutral',
            },
        ];
    }

    private static function headline(string $value): string
    {
        return filled($value)
            ? str($value)->replace(['_', '-'], ' ')->headline()->toString()
            : '-';
    }
}
