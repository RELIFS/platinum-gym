<?php

namespace App\Features\Shared\Support;

class ComposeBirthDate
{
    public static function fromParts(mixed $year, mixed $month, mixed $day): ?string
    {
        if (! is_numeric($year) || ! is_numeric($month) || ! is_numeric($day)) {
            return null;
        }

        $year = (int) $year;
        $month = (int) $month;
        $day = (int) $day;

        if (! checkdate($month, $day, $year)) {
            return null;
        }

        return sprintf('%04d-%02d-%02d', $year, $month, $day);
    }

    public static function fromDisplay(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $value = trim($value);

        if (! preg_match('/^(\d{2})\/(\d{2})\/(\d{4})$/', $value, $matches)) {
            return null;
        }

        return self::fromParts($matches[3], $matches[2], $matches[1]);
    }
}
