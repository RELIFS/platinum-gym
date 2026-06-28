<?php

namespace App\Features\Shared\Support;

use Carbon\CarbonImmutable;

class IndonesianDateFormat
{
    public static function dateFromDisplay(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $value = trim($value);

        if (! preg_match('/^(\d{2})\/(\d{2})\/(\d{4})$/', $value, $matches)) {
            return null;
        }

        if (! checkdate((int) $matches[2], (int) $matches[1], (int) $matches[3])) {
            return null;
        }

        return sprintf('%04d-%02d-%02d', (int) $matches[3], (int) $matches[2], (int) $matches[1]);
    }

    public static function dateTimeFromDisplay(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $value = trim($value);

        if (! preg_match('/^(\d{2})\/(\d{2})\/(\d{4})(?:\s+(\d{2}):(\d{2}))?$/', $value, $matches)) {
            return null;
        }

        if (! checkdate((int) $matches[2], (int) $matches[1], (int) $matches[3])) {
            return null;
        }

        $hour = (int) ($matches[4] ?? 0);
        $minute = (int) ($matches[5] ?? 0);

        if ($hour > 23 || $minute > 59) {
            return null;
        }

        return sprintf('%04d-%02d-%02dT%02d:%02d', (int) $matches[3], (int) $matches[2], (int) $matches[1], $hour, $minute);
    }

    public static function displayDate(mixed $value): string
    {
        return self::format($value, 'd/m/Y');
    }

    public static function displayDateTime(mixed $value): string
    {
        return self::format($value, 'd/m/Y H:i');
    }

    private static function format(mixed $value, string $format): string
    {
        if (! filled($value)) {
            return '';
        }

        try {
            return CarbonImmutable::parse((string) $value)->format($format);
        } catch (\Throwable) {
            return '';
        }
    }
}
