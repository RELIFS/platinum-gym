<?php

namespace App\Features\MemberPortal\Actions;

use App\Features\MemberPortal\Support\MemberPackageEligibility;
use App\Models\Member;
use App\Models\Package;
use RuntimeException;

class EnsureMemberCheckoutEligibilityAction
{
    public function handle(Member $member, Package $package): void
    {
        $member->loadMissing('user');

        $this->ensureBasicProfile($member, $package);

        if (MemberPackageEligibility::requiresFemaleMember($package)) {
            $this->ensureFemaleMember($member);
        }

        if ($this->isStudentPackage($package)) {
            $this->ensureStudentEligibility($member, $package);
        }

        if (MemberPackageEligibility::isPersonalTrainerPackage($package) && ! MemberPackageEligibility::hasActiveGymMembership($member)) {
            throw new RuntimeException('Personal Trainer hanya tersedia untuk member dengan membership Gym aktif.');
        }
    }

    private function ensureBasicProfile(Member $member, Package $package): void
    {
        if (! MemberPackageEligibility::hasCompleteBasicProfile($member)) {
            $packageType = (string) $package->package_kind === 'membership' ? 'membership' : 'paket sesi';

            throw new RuntimeException("Lengkapi profil dan foto profil sebelum checkout {$packageType}.");
        }
    }

    private function ensureStudentEligibility(Member $member, Package $package): void
    {
        if (! $member->is_student) {
            throw new RuntimeException('Paket mahasiswa hanya tersedia untuk member dengan status mahasiswa.');
        }

        if (blank($member->student_proof_path)) {
            throw new RuntimeException('Upload KTM atau screenshot akun portal mahasiswa sebelum checkout paket mahasiswa.');
        }

        $maxAge = (int) ($package->max_age ?: 22);
        if ($member->birth_date && $member->birth_date->age > $maxAge) {
            throw new RuntimeException('Umur member tidak memenuhi syarat paket mahasiswa.');
        }

    }

    private function ensureFemaleMember(Member $member): void
    {
        if ((string) $member->gender !== 'female') {
            throw new RuntimeException(blank($member->gender) ? 'Lengkapi jenis kelamin di profil untuk mengecek paket ini.' : 'Paket ini khusus member perempuan.');
        }
    }

    public function isStudentPackage(Package $package): bool
    {
        return MemberPackageEligibility::isStudentPackage($package);
    }
}
