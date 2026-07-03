<?php

namespace App\Features\CheckIns\Actions;

use App\Models\ClassAttendance;
use App\Models\ClassEnrollment;
use App\Models\GymCheckIn;
use App\Models\Member;
use App\Models\MemberPackageSession;
use App\Models\MemberPackageSessionUsage;
use App\Models\Membership;
use App\Models\QrToken;
use App\Support\MemberQrAccess;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class ConfirmMemberQrCheckInAction
{
    public const CHECK_IN_MEMBERSHIP = 'check_in_membership';

    public const USE_PACKAGE_SESSION = 'use_package_session';

    public const CHECK_IN_AND_USE_SESSION = 'check_in_and_use_session';

    /**
     * @return array{member_name: string, check_in?: GymCheckIn|null, usage?: MemberPackageSessionUsage|null}
     */
    public function handle(string $token, string $action, int $adminUserId, ?int $packageSessionId = null, ?int $classEnrollmentId = null, ?string $requestKey = null): array
    {
        return DB::transaction(function () use ($token, $action, $adminUserId, $packageSessionId, $classEnrollmentId, $requestKey): array {
            $qrToken = QrToken::query()
                ->where('token', $token)
                ->where('purpose', 'member')
                ->where('is_revoked', false)
                ->lockForUpdate()
                ->first();

            if (! $qrToken || ! ($qrToken->tokenable instanceof Member)) {
                throw new RuntimeException('QR member tidak valid.');
            }

            $member = Member::query()->with('user')->lockForUpdate()->findOrFail($qrToken->tokenable_id);
            $membership = $this->activeMembership($member);

            if (! $membership && in_array($action, [self::CHECK_IN_MEMBERSHIP, self::CHECK_IN_AND_USE_SESSION], true)) {
                throw new RuntimeException('Membership aktif tidak ditemukan.');
            }

            $today = now()->toDateString();
            $checkIn = null;
            $usage = null;

            if (in_array($action, [self::CHECK_IN_MEMBERSHIP, self::CHECK_IN_AND_USE_SESSION], true)) {
                if ($member->gymCheckIns()->whereDate('check_in_date', $today)->lockForUpdate()->exists()) {
                    throw new RuntimeException('Member sudah check-in hari ini.');
                }

                $membership->startDurationOn($today);

                $checkIn = GymCheckIn::create([
                    'member_id' => $member->id,
                    'membership_id' => $membership->id,
                    'check_in_date' => $today,
                    'check_in_at' => now(),
                    'method' => 'qr',
                    'scanned_by' => $adminUserId,
                ]);
            }

            if (in_array($action, [self::USE_PACKAGE_SESSION, self::CHECK_IN_AND_USE_SESSION], true)) {
                if (! $packageSessionId) {
                    throw new RuntimeException('Pilih paket sesi yang akan digunakan.');
                }

                $usage = $this->usePackageSession($member, $packageSessionId, $adminUserId, $checkIn, $classEnrollmentId, $requestKey, (bool) $membership);
            }

            $qrToken->forceFill(['last_used_at' => now()])->save();

            return [
                'member_name' => $member->user?->name ?? $member->member_code,
                'check_in' => $checkIn?->refresh(),
                'usage' => $usage?->refresh(),
            ];
        });
    }

    private function activeMembership(Member $member): ?Membership
    {
        $today = now()->toDateString();

        $startedMembership = $member->memberships()
            ->with('package')
            ->startedAndCurrent($today)
            ->orderBy('end_date')
            ->lockForUpdate()
            ->first();

        if ($startedMembership) {
            return $startedMembership;
        }

        return $member->memberships()
            ->with('package')
            ->awaitingFirstCheckIn()
            ->orderBy('activated_at')
            ->orderBy('created_at')
            ->lockForUpdate()
            ->first();
    }

    private function usePackageSession(Member $member, int $packageSessionId, int $adminUserId, ?GymCheckIn $checkIn, ?int $classEnrollmentId, ?string $requestKey, bool $hasActiveMembership): ?MemberPackageSessionUsage
    {
        if (filled($requestKey)) {
            $existingUsage = MemberPackageSessionUsage::query()->where('request_key', $requestKey)->first();

            if ($existingUsage) {
                return $existingUsage;
            }
        }

        $today = now()->toDateString();
        $session = MemberPackageSession::query()
            ->with('package')
            ->where('id', $packageSessionId)
            ->where('member_id', $member->id)
            ->where('status', 'active')
            ->where(function ($query) use ($today): void {
                $query->whereNull('expired_at')
                    ->orWhereDate('expired_at', '>=', $today);
            })
            ->lockForUpdate()
            ->first();

        if (! $session) {
            throw new RuntimeException('Paket sesi aktif tidak tersedia atau sesi sudah habis.');
        }

        $isClassSession = MemberQrAccess::isStandaloneSessionType($session->package?->type);

        if (! $hasActiveMembership && ! $isClassSession) {
            throw new RuntimeException('Membership aktif diperlukan untuk menggunakan paket sesi ini.');
        }

        $classEnrollment = null;

        if ($isClassSession) {
            $classEnrollment = $this->eligibleClassEnrollment($member, $session, $classEnrollmentId, $today);
        } else {
            $existingUsageToday = MemberPackageSessionUsage::query()
                ->where('member_package_session_id', $packageSessionId)
                ->where('member_id', $member->id)
                ->whereDate('usage_date', $today)
                ->lockForUpdate()
                ->first();

            if ($existingUsageToday) {
                return $checkIn ? null : $existingUsageToday;
            }
        }

        if ((int) $session->remaining_sessions <= 0) {
            throw new RuntimeException('Paket sesi aktif tidak tersedia atau sesi sudah habis.');
        }

        $session->forceFill([
            'used_sessions' => (int) $session->used_sessions + 1,
            'remaining_sessions' => max((int) $session->remaining_sessions - 1, 0),
        ])->save();

        if ((int) $session->remaining_sessions === 0) {
            $session->forceFill(['status' => 'exhausted'])->save();
        }

        if ($classEnrollment) {
            ClassAttendance::create([
                'enrollment_id' => $classEnrollment->id,
                'schedule_id' => $classEnrollment->schedule_id,
                'member_id' => $member->id,
                'attendance_date' => $today,
                'attended_at' => now(),
                'method' => 'admin_qr',
                'status' => 'present',
                'scanned_by' => $adminUserId,
            ]);

            $classEnrollment->forceFill(['status' => 'attended'])->save();
        }

        return MemberPackageSessionUsage::create([
            'member_package_session_id' => $session->id,
            'member_id' => $member->id,
            'gym_check_in_id' => $checkIn?->id,
            'class_enrollment_id' => $classEnrollment?->id,
            'usage_date' => $today,
            'used_at' => now(),
            'method' => 'admin_qr',
            'recorded_by' => $adminUserId,
            'request_key' => filled($requestKey) ? $requestKey : null,
        ]);
    }

    private function eligibleClassEnrollment(Member $member, MemberPackageSession $session, ?int $classEnrollmentId, string $today): ClassEnrollment
    {
        if (! $classEnrollmentId) {
            throw new RuntimeException('Tidak ada booking kelas confirmed hari ini untuk paket sesi ini.');
        }

        $enrollment = ClassEnrollment::query()
            ->whereKey($classEnrollmentId)
            ->where('member_id', $member->id)
            ->lockForUpdate()
            ->first();

        if (! $enrollment) {
            throw new RuntimeException('Booking kelas tidak valid untuk member ini.');
        }

        $enrollment->loadMissing(['schedule.gymClass', 'schedule.trainer']);

        if ($enrollment->status !== 'confirmed') {
            throw new RuntimeException('Booking kelas harus sudah dikonfirmasi admin sebelum sesi digunakan.');
        }

        if (! $enrollment->session_date?->isSameDay($today)) {
            throw new RuntimeException('Gunakan Sesi hanya bisa untuk booking kelas hari ini.');
        }

        $schedule = $enrollment->schedule;
        $gymClass = $schedule?->gymClass;

        if (! $schedule || ! $schedule->is_active || ! $gymClass || ! $gymClass->is_active) {
            throw new RuntimeException('Jadwal kelas tidak aktif.');
        }

        if ((int) $schedule->day_of_week !== (int) $enrollment->session_date->dayOfWeekIso) {
            throw new RuntimeException('Tanggal booking tidak sesuai jadwal kelas.');
        }

        if (! $this->packageMatchesClass((string) $session->package?->type, $gymClass->required_package_type, $gymClass->class_type)) {
            throw new RuntimeException('Paket sesi tidak sesuai dengan kelas yang dibooking.');
        }

        if ($session->trainer_id && (int) $schedule->trainer_id !== (int) $session->trainer_id) {
            throw new RuntimeException('Paket sesi tidak sesuai dengan coach pada jadwal booking.');
        }

        if (ClassAttendance::query()->where('enrollment_id', $enrollment->id)->lockForUpdate()->exists()) {
            throw new RuntimeException('Booking kelas ini sudah digunakan.');
        }

        if (MemberPackageSessionUsage::query()->where('class_enrollment_id', $enrollment->id)->lockForUpdate()->exists()) {
            throw new RuntimeException('Sesi untuk booking kelas ini sudah digunakan.');
        }

        return $enrollment;
    }

    private function packageMatchesClass(string $packageType, ?string $requiredPackageType, ?string $classType): bool
    {
        if (filled($requiredPackageType)) {
            return $requiredPackageType === $packageType;
        }

        return $classType === $packageType;
    }
}
