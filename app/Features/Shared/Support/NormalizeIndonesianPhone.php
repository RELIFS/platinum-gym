<?php

namespace App\Features\Shared\Support;

final class NormalizeIndonesianPhone
{
    public static function toLocalMobile(?string $phone): string
    {
        $normalized = preg_replace('/\D+/', '', (string) $phone) ?? '';

        if (str_starts_with($normalized, '62')) {
            return '0'.substr($normalized, 2);
        }

        return $normalized;
    }
}
