<?php

namespace App\Features\CheckIns\Actions;

use App\Models\GymCheckIn;
use App\Models\Member;
use App\Models\Membership;
use App\Models\QrToken;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class ScanMemberQrAction
{
    public function handle(string $token, int $adminUserId): GymCheckIn
    {
        return DB::transaction(function () use ($token, $adminUserId): GymCheckIn {
            $qrToken = QrToken::query()
                ->where('token', $token)
                ->where('purpose', 'member')
                ->where('is_revoked', false)
                ->lockForUpdate()
                ->first();

            if (! $qrToken || ! ($qrToken->tokenable instanceof Member)) {
                throw new RuntimeException('QR member tidak valid.');
            }

            $member = $qrToken->tokenable;
            $membership = $this->activeMembership($member);

            if (! $membership) {
                throw new RuntimeException('Membership aktif tidak ditemukan.');
            }

            $today = now()->toDateString();

            if ($member->gymCheckIns()->whereDate('check_in_date', $today)->exists()) {
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

            $qrToken->forceFill(['last_used_at' => now()])->save();

            return $checkIn->refresh();
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
}
