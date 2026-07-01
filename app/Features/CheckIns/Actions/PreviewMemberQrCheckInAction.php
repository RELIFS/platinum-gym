<?php

namespace App\Features\CheckIns\Actions;

use App\Models\Member;
use App\Models\Membership;
use App\Models\QrToken;
use App\Support\MemberQrAccess;
use RuntimeException;

class PreviewMemberQrCheckInAction
{
    /**
     * @return array<string, mixed>
     */
    public function handle(string $token): array
    {
        $qrToken = QrToken::query()
            ->with('tokenable.user')
            ->where('token', $token)
            ->where('purpose', 'member')
            ->where('is_revoked', false)
            ->first();

        if (! $qrToken || ! ($qrToken->tokenable instanceof Member)) {
            throw new RuntimeException('QR member tidak valid.');
        }

        $member = $qrToken->tokenable->loadMissing('user');
        $membership = $this->activeMembership($member);

        $today = now()->toDateString();
        $todayCheckIn = $member->gymCheckIns()
            ->whereDate('check_in_date', $today)
            ->latest('check_in_at')
            ->first();

        $sessionsQuery = MemberQrAccess::activePackageSessionsQuery($member, $today);

        if (! $membership) {
            $sessionsQuery->whereHas('package', fn ($query) => $query->whereIn('type', MemberQrAccess::standaloneSessionTypes()));
        }

        $sessions = $sessionsQuery->orderByDesc('remaining_sessions')->get();

        if (! $membership && $sessions->isEmpty()) {
            throw new RuntimeException('Membership atau paket sesi aktif tidak ditemukan.');
        }

        return [
            'member_id' => $member->id,
            'member_code' => $member->member_code,
            'name' => $member->user?->name ?? $member->member_code,
            'email' => $member->user?->email,
            'phone' => $member->user?->phone,
            'avatar' => $member->user?->avatar,
            'membership' => $membership ? [
                'id' => $membership->id,
                'name' => $membership->package?->name ?? $membership->code,
                'end_date' => $membership->end_date?->translatedFormat('d M Y') ?? 'Mulai saat check-in pertama',
                'status' => $membership->status,
            ] : null,
            'qr' => [
                'status' => $membership ? 'Aktif' : 'Aktif untuk sesi',
                'expires_at' => null,
                'last_used_at' => $qrToken->last_used_at?->translatedFormat('d M Y H:i'),
            ],
            'today_check_in' => $todayCheckIn ? [
                'time' => $todayCheckIn->check_in_at?->format('H:i'),
                'method' => str((string) $todayCheckIn->method)->replace('_', ' ')->headline()->toString(),
            ] : null,
            'sessions' => $sessions->map(fn ($session): array => [
                'id' => $session->id,
                'name' => $session->package?->name ?? $session->code,
                'remaining' => (int) $session->remaining_sessions,
                'total' => (int) $session->total_sessions,
                'trainer' => $session->trainer?->name,
                'expired_at' => $session->expired_at?->translatedFormat('d M Y'),
            ])->values()->all(),
        ];
    }

    private function activeMembership(Member $member): ?Membership
    {
        $today = now()->toDateString();

        $startedMembership = $member->memberships()
            ->with('package')
            ->startedAndCurrent($today)
            ->orderBy('end_date')
            ->first();

        if ($startedMembership) {
            return $startedMembership;
        }

        return $member->memberships()
            ->with('package')
            ->awaitingFirstCheckIn()
            ->orderBy('activated_at')
            ->orderBy('created_at')
            ->first();
    }
}
