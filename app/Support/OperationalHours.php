<?php

namespace App\Support;

class OperationalHours
{
    /**
     * @return array{monday_saturday: string, sunday: string}
     */
    public static function normalize(mixed $value): array
    {
        if (is_string($value)) {
            $decoded = json_decode($value, true);
            $value = is_array($decoded) ? $decoded : [];
        }

        $value = is_array($value) ? $value : [];

        return [
            'monday_saturday' => trim((string) ($value['monday_saturday'] ?? '08:00-22:00')) ?: '08:00-22:00',
            'sunday' => trim((string) ($value['sunday'] ?? 'Tutup')) ?: 'Tutup',
        ];
    }

    /**
     * @param  array{monday_saturday?: string, sunday?: string}  $hours
     * @return array<int, array{label: string, value: string}>
     */
    public static function rows(array $hours): array
    {
        $hours = self::normalize($hours);

        return [
            ['label' => 'Senin-Sabtu', 'value' => $hours['monday_saturday']],
            ['label' => 'Minggu', 'value' => $hours['sunday']],
        ];
    }

    /**
     * @param  array{monday_saturday?: string, sunday?: string}  $hours
     * @return array<int, string>
     */
    public static function schema(array $hours): array
    {
        $hours = self::normalize($hours);

        return ['Mo-Sa '.$hours['monday_saturday']];
    }

    /**
     * @param  array{monday_saturday?: string, sunday?: string}  $hours
     */
    public static function sentence(array $hours): string
    {
        $hours = self::normalize($hours);

        $sunday = mb_strtolower($hours['sunday']) === 'tutup' ? 'tutup' : $hours['sunday'];

        return 'Jam operasional Platinum Gym: Senin-Sabtu '.$hours['monday_saturday'].', Minggu '.$sunday.'.';
    }
}
