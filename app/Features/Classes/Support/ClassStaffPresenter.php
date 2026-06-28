<?php

namespace App\Features\Classes\Support;

use App\Models\ClassSchedule;
use App\Models\GymClass;
use App\Models\Trainer;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class ClassStaffPresenter
{
    public static function roleLabel(ClassSchedule|GymClass|null $classSource): string
    {
        $type = self::classKey($classSource);

        return match ($type) {
            'aerobic', 'zumba' => 'Instruktur',
            'poundfit' => 'Pro',
            default => 'Coach',
        };
    }

    public static function displayName(?Trainer $trainer, ClassSchedule|GymClass|null $classSource): string
    {
        if (! $trainer) {
            return '-';
        }

        $name = (string) $trainer->name;
        $type = self::classKey($classSource);

        if (in_array($type, ['aerobic', 'zumba', 'poundfit'], true)) {
            $displayName = Str::of($name)->replaceStart('Coach ', '');

            if ($type !== 'zumba') {
                $displayName = $displayName->replaceStart('Zin ', '');
            }

            return $displayName->trim()->toString();
        }

        return $name;
    }

    public static function memberBookingDisplayName(?Trainer $trainer, ClassSchedule|GymClass|null $classSource): string
    {
        $displayName = self::displayName($trainer, $classSource);

        if (self::classKey($classSource) === 'aerobic' && in_array($displayName, ['Ola', 'Irgo'], true)) {
            return 'Ola atau Irgo';
        }

        return $displayName;
    }

    /**
     * @param  Collection<int, ClassSchedule>  $schedules
     */
    public static function displayNames(Collection $schedules): string
    {
        $names = $schedules
            ->map(fn (ClassSchedule $schedule): string => self::displayName($schedule->trainer, $schedule))
            ->filter(fn (string $name): bool => $name !== '-')
            ->unique()
            ->values();

        if ($names->isEmpty()) {
            return '-';
        }

        if ($names->count() === 1) {
            return $names->first();
        }

        return $names->slice(0, -1)->implode(', ').' atau '.$names->last();
    }

    public static function timeLabel(ClassSchedule $schedule): string
    {
        if (in_array(self::classKey($schedule), ['aerobic', 'zumba'], true)) {
            return substr((string) $schedule->start_time, 0, 5);
        }

        return substr((string) $schedule->start_time, 0, 5).' - '.substr((string) $schedule->end_time, 0, 5);
    }

    public static function classKey(ClassSchedule|GymClass|null $classSource): string
    {
        $gymClass = $classSource instanceof ClassSchedule ? $classSource->gymClass : $classSource;
        $name = Str::of((string) $gymClass?->name)->lower()->toString();
        $type = Str::of((string) $gymClass?->class_type)->lower()->toString();

        return match (true) {
            str_contains($name, 'aerobic') || $type === 'aerobic' => 'aerobic',
            str_contains($name, 'zumba') || $type === 'zumba' => 'zumba',
            str_contains($name, 'poundfit') || $type === 'poundfit' => 'poundfit',
            str_contains($name, 'muaythai') || $type === 'muaythai' => 'muaythai',
            default => $type ?: 'class',
        };
    }
}
