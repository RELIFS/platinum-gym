<?php

namespace App\Features\Auth\Support;

use Illuminate\Support\Str;

class MaskedEmail
{
    public static function forDisplay(string $email): string
    {
        $email = trim($email);

        if (! str_contains($email, '@')) {
            return Str::mask($email, '*', 1);
        }

        [$localPart, $domain] = explode('@', $email, 2);

        if ($localPart === '' || $domain === '') {
            return Str::mask($email, '*', 1);
        }

        $visibleLength = strlen($localPart) >= 3 ? 3 : 1;
        $maskLength = max(strlen($localPart) - $visibleLength, 1);

        return substr($localPart, 0, $visibleLength).str_repeat('*', $maskLength).'@'.$domain;
    }
}
