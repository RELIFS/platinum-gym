<?php

namespace App\Features\MemberPortal\Support;

use App\Models\Member;
use App\Models\Package;

class MemberPackageEligibility
{
    /**
     * @return array{can_checkout: bool, reason: string|null, cta_route: string|null, cta_label: string|null, button_label: string, is_student_package: bool, is_pt_package: bool, is_gender_restricted: bool}
     */
    public static function forPackage(Member $member, Package $package, bool $hasActiveMembership, bool $hasActiveGymMembership, ?string $profileRoute = null, ?string $membershipRoute = null): array
    {
        $isMembership = (string) $package->package_kind === 'membership';
        $isStudentPackage = self::isStudentPackage($package);
        $isPtPackage = self::isPersonalTrainerPackage($package);
        $buttonLabel = $isMembership ? 'Checkout Membership' : 'Checkout Paket Sesi';

        if ($isMembership && self::missingBasicProfile($member)) {
            return self::blocked('Lengkapi profil dan foto profil sebelum checkout membership.', 'Lengkapi data', $profileRoute, $buttonLabel, $package, $isStudentPackage, $isPtPackage);
        }

        if (self::requiresFemaleMember($package) && (string) $member->gender !== 'female') {
            if (blank($member->gender)) {
                return self::blocked('Lengkapi jenis kelamin di profil untuk mengecek paket ini.', 'Lengkapi data', $profileRoute, $buttonLabel, $package, $isStudentPackage, $isPtPackage);
            }

            return self::blocked('Paket ini khusus member perempuan.', null, null, 'Khusus Perempuan', $package, $isStudentPackage, $isPtPackage);
        }

        if ($isMembership && $isStudentPackage) {
            $studentBlock = self::studentBlock($member, $package, $buttonLabel, $profileRoute);

            if ($studentBlock !== null) {
                return $studentBlock;
            }
        }

        if ($isPtPackage && ! $hasActiveGymMembership) {
            return self::blocked('Personal Trainer hanya tersedia untuk member dengan membership Gym aktif.', 'Pilih Membership Gym', $membershipRoute, 'Butuh Membership Gym', $package, $isStudentPackage, $isPtPackage);
        }

        if (! $isMembership && (bool) $package->requires_active_membership && ! $hasActiveMembership) {
            return self::blocked('Paket ini membutuhkan membership aktif.', 'Lihat membership', $membershipRoute, $buttonLabel, $package, $isStudentPackage, $isPtPackage);
        }

        return [
            'can_checkout' => true,
            'reason' => null,
            'cta_route' => null,
            'cta_label' => null,
            'button_label' => $buttonLabel,
            'is_student_package' => $isStudentPackage,
            'is_pt_package' => $isPtPackage,
            'is_gender_restricted' => self::requiresFemaleMember($package),
        ];
    }

    public static function isStudentPackage(Package $package): bool
    {
        return str((string) $package->category)->lower()->toString() === 'mahasiswa' || filled($package->max_age);
    }

    public static function isPersonalTrainerPackage(Package $package): bool
    {
        return (string) $package->type === 'pt';
    }

    public static function requiresFemaleMember(Package $package): bool
    {
        return (string) $package->gender_restriction === 'female';
    }

    public static function hasActiveGymMembership(Member $member): bool
    {
        $today = now()->toDateString();

        return $member->memberships()
            ->where('status', 'active')
            ->whereDate('start_date', '<=', $today)
            ->whereDate('end_date', '>=', $today)
            ->whereHas('package', fn ($query) => $query->whereIn('type', ['gym', 'include']))
            ->exists();
    }

    private static function missingBasicProfile(Member $member): bool
    {
        $member->loadMissing('user');
        $user = $member->user;

        return ! $user
            || blank($user->name)
            || blank($user->email)
            || blank($user->phone)
            || blank($user->avatar)
            || blank($member->gender)
            || ! $member->birth_date;
    }

    /**
     * @return array{can_checkout: bool, reason: string|null, cta_route: string|null, cta_label: string|null, button_label: string, is_student_package: bool, is_pt_package: bool, is_gender_restricted: bool}|null
     */
    private static function studentBlock(Member $member, Package $package, string $buttonLabel, ?string $profileRoute): ?array
    {
        if (! $member->is_student) {
            return self::blocked('Aktifkan status mahasiswa di profil.', 'Lengkapi data', $profileRoute, $buttonLabel, $package, true, false);
        }

        if (blank($member->student_id_number)) {
            return self::blocked('Lengkapi NIM yang terdaftar di PDDIKTI.', 'Lengkapi data', $profileRoute, $buttonLabel, $package, true, false);
        }

        $maxAge = (int) ($package->max_age ?: 22);
        if ($member->birth_date && $member->birth_date->age > $maxAge) {
            return self::blocked('Umur tidak memenuhi syarat paket mahasiswa.', 'Cek profil', $profileRoute, $buttonLabel, $package, true, false);
        }

        return null;
    }

    /**
     * @return array{can_checkout: bool, reason: string|null, cta_route: string|null, cta_label: string|null, button_label: string, is_student_package: bool, is_pt_package: bool, is_gender_restricted: bool}
     */
    private static function blocked(string $reason, ?string $ctaLabel, ?string $ctaRoute, string $buttonLabel, Package $package, bool $isStudentPackage, bool $isPtPackage): array
    {
        return [
            'can_checkout' => false,
            'reason' => $reason,
            'cta_route' => $ctaRoute,
            'cta_label' => $ctaLabel,
            'button_label' => $buttonLabel,
            'is_student_package' => $isStudentPackage,
            'is_pt_package' => $isPtPackage,
            'is_gender_restricted' => self::requiresFemaleMember($package),
        ];
    }
}
