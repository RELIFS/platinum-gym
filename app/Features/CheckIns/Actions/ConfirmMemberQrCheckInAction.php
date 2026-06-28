<?php

namespace App\Features\CheckIns\Actions;

use App\Models\GymCheckIn;
use App\Models\Member;
use App\Models\MemberPackageSession;
use App\Models\MemberPackageSessionUsage;
use App\Models\Membership;
use App\Models\QrToken;
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
    public function handle(string $token, string $action, int $adminUserId, ?int $packageSessionId = null, ?string $requestKey = null): array
    {
        return DB::transaction(function () use ($token, $action, $adminUserId, $packageSessionId, $requestKey): array {
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

            if (! $membership) {
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

                $usage = $this->usePackageSession($member, $packageSessionId, $adminUserId, $checkIn, $requestKey);
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

    private function usePackageSession(Member $member, int $packageSessionId, int $adminUserId, ?GymCheckIn $checkIn, ?string $requestKey): ?MemberPackageSessionUsage
    {
        if (filled($requestKey)) {
            $existingUsage = MemberPackageSessionUsage::query()->where('request_key', $requestKey)->first();

            if ($existingUsage) {
                return $existingUsage;
            }
        }

        $today = now()->toDateString();
        $existingUsageToday = MemberPackageSessionUsage::query()
            ->where('member_package_session_id', $packageSessionId)
            ->where('member_id', $member->id)
            ->whereDate('usage_date', $today)
            ->lockForUpdate()
            ->first();

        if ($existingUsageToday) {
            return $checkIn ? null : $existingUsageToday;
        }

        $session = MemberPackageSession::query()
            ->where('id', $packageSessionId)
            ->where('member_id', $member->id)
            ->where('status', 'active')
            ->where('remaining_sessions', '>', 0)
            ->where(function ($query) use ($today): void {
                $query->whereNull('expired_at')
                    ->orWhereDate('expired_at', '>=', $today);
            })
            ->lockForUpdate()
            ->first();

        if (! $session) {
            throw new RuntimeException('Paket sesi aktif tidak tersedia atau sesi sudah habis.');
        }

        $session->forceFill([
            'used_sessions' => (int) $session->used_sessions + 1,
            'remaining_sessions' => max((int) $session->remaining_sessions - 1, 0),
        ])->save();

        if ((int) $session->remaining_sessions === 0) {
            $session->forceFill(['status' => 'exhausted'])->save();
        }

        return MemberPackageSessionUsage::create([
            'member_package_session_id' => $session->id,
            'member_id' => $member->id,
            'gym_check_in_id' => $checkIn?->id,
            'usage_date' => $today,
            'used_at' => now(),
            'method' => 'admin_qr',
            'recorded_by' => $adminUserId,
            'request_key' => filled($requestKey) ? $requestKey : null,
        ]);
    }
}
