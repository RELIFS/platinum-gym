<?php

namespace App\Features\PublicWebsite\Support;

use App\Models\Trainer;
use Illuminate\Support\Str;

class PublicTrainerPresenter
{
    public static function displayName(Trainer $trainer): string
    {
        $name = trim((string) $trainer->name);

        if (in_array(self::specializationKey($trainer), ['aerobic', 'zumba', 'poundfit'], true)) {
            return trim((string) preg_replace('/^Coach\s+/i', '', $name));
        }

        return $name;
    }

    public static function roleWithSpecialization(Trainer $trainer): string
    {
        $role = self::roleLabel($trainer);
        $specialization = trim((string) $trainer->specialization);

        if ($specialization === '') {
            return $role;
        }

        return $role.' '.$specialization;
    }

    public static function initial(Trainer $trainer): string
    {
        return Str::of(self::displayName($trainer))->substr(0, 1)->upper()->toString();
    }

    private static function roleLabel(Trainer $trainer): string
    {
        return match (self::specializationKey($trainer)) {
            'aerobic', 'zumba' => 'Instruktur',
            'poundfit' => 'Pro',
            default => 'Coach',
        };
    }

    private static function specializationKey(Trainer $trainer): string
    {
        $specialization = Str::of((string) $trainer->specialization)->lower()->toString();

        return match (true) {
            str_contains($specialization, 'aerobic') => 'aerobic',
            str_contains($specialization, 'zumba') => 'zumba',
            str_contains($specialization, 'poundfit') => 'poundfit',
            default => 'coach',
        };
    }
}
