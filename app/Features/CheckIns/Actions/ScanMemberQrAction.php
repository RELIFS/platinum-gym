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
        return $member->memberships()
            ->where('status', 'active')
            ->whereDate('start_date', '<=', now()->toDateString())
            ->whereDate('end_date', '>=', now()->toDateString())
            ->orderBy('end_date')
            ->first();
    }
}
