<?php

namespace App\Features\CheckIns\Actions;

use App\Models\GymCheckIn;
use App\Models\Member;
use App\Models\Membership;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class ManualMemberCheckInAction
{
    public function handle(Member $member, int $adminUserId): GymCheckIn
    {
        return DB::transaction(function () use ($member, $adminUserId): GymCheckIn {
            $member = Member::query()->lockForUpdate()->findOrFail($member->id);
            $membership = $this->activeMembership($member);

            if (! $membership) {
                throw new RuntimeException('Membership aktif tidak ditemukan.');
            }

            $today = now()->toDateString();

            if ($member->gymCheckIns()->whereDate('check_in_date', $today)->exists()) {
                throw new RuntimeException('Member sudah check-in hari ini.');
            }

            return GymCheckIn::create([
                'member_id' => $member->id,
                'membership_id' => $membership->id,
                'check_in_date' => $today,
                'check_in_at' => now(),
                'method' => 'manual',
                'scanned_by' => $adminUserId,
            ])->refresh();
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
