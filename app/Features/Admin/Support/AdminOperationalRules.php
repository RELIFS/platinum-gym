<?php

namespace App\Features\Admin\Support;

use App\Models\ClassSchedule;
use App\Models\Member;
use App\Models\Package;
use App\Models\Trainer;
use Illuminate\Support\Collection;

class AdminOperationalRules
{
    /** @return array<string, array<int, string>> */
    public static function bookingMemberScheduleAccess(Collection $members, Collection $schedules): array
    {
        return $members
            ->mapWithKeys(fn (Member $member): array => [
                (string) $member->id => $schedules
                    ->filter(fn (ClassSchedule $schedule): bool => self::memberCanBookSchedule($member, $schedule))
                    ->pluck('id')
                    ->map(fn (mixed $id): string => (string) $id)
                    ->values()
                    ->all(),
            ])
            ->all();
    }

    public static function memberCanBookSchedule(Member $member, ClassSchedule $schedule): bool
    {
        $schedule->loadMissing('gymClass');
        $gymClass = $schedule->gymClass;

        if (! $schedule->is_active || ! $gymClass?->is_active) {
            return false;
        }

        return match ((string) $gymClass->access_type) {
            'included' => self::memberHasMembershipType($member, (string) $gymClass->required_package_type),
            'session_based' => self::memberHasPackageSessionForSchedule($member, $schedule, (string) $gymClass->required_package_type),
            default => false,
        };
    }

    /** @return array<string, array{requires_trainer: bool, trainer_specialization: string|null}> */
    public static function paymentPackageTrainerRules(Collection $packages): array
    {
        return $packages
            ->mapWithKeys(fn (Package $package): array => [
                (string) $package->id => [
                    'requires_trainer' => self::packageRequiresTrainer($package),
                    'trainer_specialization' => self::trainerSpecializationFor($package),
                ],
            ])
            ->all();
    }

    /** @return array<string, array<int, array{id: string, label: string}>> */
    public static function paymentTrainerOptionsByPackage(Collection $packages): array
    {
        $specializations = $packages
            ->map(fn (Package $package): ?string => self::trainerSpecializationFor($package))
            ->filter()
            ->unique()
            ->values();

        if ($specializations->isEmpty()) {
            return [];
        }

        $trainersBySpecialization = Trainer::query()
            ->where('is_active', true)
            ->whereIn('specialization', $specializations->all())
            ->orderBy('name')
            ->get(['id', 'name', 'specialization'])
            ->groupBy('specialization');

        return $packages
            ->mapWithKeys(function (Package $package) use ($trainersBySpecialization): array {
                $specialization = self::trainerSpecializationFor($package);

                if (! $specialization) {
                    return [(string) $package->id => []];
                }

                return [(string) $package->id => $trainersBySpecialization
                    ->get($specialization, collect())
                    ->map(fn (Trainer $trainer): array => [
                        'id' => (string) $trainer->id,
                        'label' => (string) $trainer->name,
                    ])
                    ->values()
                    ->all()];
            })
            ->all();
    }

    public static function packageRequiresTrainer(Package $package): bool
    {
        return $package->package_kind !== 'membership'
            && in_array((string) $package->type, ['pt', 'muaythai'], true);
    }

    public static function trainerMatchesPackage(Package $package, Trainer $trainer): bool
    {
        $specialization = self::trainerSpecializationFor($package);

        return $trainer->is_active
            && filled($specialization)
            && (string) $trainer->specialization === $specialization;
    }

    public static function trainerSpecializationFor(Package $package): ?string
    {
        if ($package->package_kind === 'membership') {
            return null;
        }

        return match ((string) $package->type) {
            'pt' => 'Personal Trainer',
            'muaythai' => 'Muaythai',
            default => null,
        };
    }

    private static function memberHasMembershipType(Member $member, string $requiredPackageType): bool
    {
        if (! $member->relationLoaded('memberships')) {
            return $member->memberships()
                ->activeForAccess()
                ->whereHas('package', function ($query) use ($requiredPackageType): void {
                    $query->where('package_kind', 'membership')
                        ->where(function ($query) use ($requiredPackageType): void {
                            $query->where('type', $requiredPackageType)
                                ->orWhere('type', 'include');
                        });
                })
                ->exists();
        }

        $today = now()->toDateString();

        return $member->memberships
            ->contains(function ($membership) use ($requiredPackageType, $today): bool {
                $package = $membership->package;
                $started = $membership->start_date && $membership->end_date
                    && $membership->start_date->toDateString() <= $today
                    && $membership->end_date->toDateString() >= $today;
                $awaitingFirstCheckIn = blank($membership->start_date) && blank($membership->end_date);

                return $membership->status === 'active'
                    && ($started || $awaitingFirstCheckIn)
                    && $package?->package_kind === 'membership'
                    && in_array((string) $package->type, [$requiredPackageType, 'include'], true);
            });
    }

    private static function memberHasPackageSessionForSchedule(Member $member, ClassSchedule $schedule, string $requiredPackageType): bool
    {
        if (! $member->relationLoaded('packageSessions')) {
            $query = $member->packageSessions()
                ->where('status', 'active')
                ->where('remaining_sessions', '>', 0)
                ->where(function ($query): void {
                    $query->whereNull('expired_at')->orWhereDate('expired_at', '>=', now()->toDateString());
                })
                ->whereHas('package', fn ($query) => $query->where('type', $requiredPackageType));

            if ($requiredPackageType === 'muaythai' && filled($schedule->trainer_id)) {
                $query->where(function ($query) use ($schedule): void {
                    $query->whereNull('trainer_id')->orWhere('trainer_id', $schedule->trainer_id);
                });
            }

            return $query->exists();
        }

        $today = now()->toDateString();

        return $member->packageSessions
            ->contains(fn ($session): bool => $session->status === 'active'
                && (int) $session->remaining_sessions > 0
                && (blank($session->expired_at) || $session->expired_at->toDateString() >= $today)
                && (string) $session->package?->type === $requiredPackageType
                && (
                    $requiredPackageType !== 'muaythai'
                    || blank($schedule->trainer_id)
                    || blank($session->trainer_id)
                    || (int) $session->trainer_id === (int) $schedule->trainer_id
                ));
    }
}
