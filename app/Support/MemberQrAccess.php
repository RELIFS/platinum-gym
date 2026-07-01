<?php

namespace App\Support;

use App\Models\Member;
use App\Models\MemberPackageSession;

class MemberQrAccess
{
    public const STANDALONE_SESSION_TYPES = ['muaythai', 'poundfit'];

    /**
     * @return array<int, string>
     */
    public static function standaloneSessionTypes(): array
    {
        return self::STANDALONE_SESSION_TYPES;
    }

    public static function isStandaloneSessionType(?string $type): bool
    {
        return in_array((string) $type, self::STANDALONE_SESSION_TYPES, true);
    }

    public static function sessionCanActivateQr(MemberPackageSession $session): bool
    {
        $session->loadMissing('package');

        return self::isStandaloneSessionType($session->package?->type);
    }

    public static function hasActiveStandaloneSession(Member $member, ?string $today = null): bool
    {
        return self::activePackageSessionsQuery($member, $today)
            ->whereHas('package', fn ($query) => $query->whereIn('type', self::STANDALONE_SESSION_TYPES))
            ->exists();
    }

    public static function activePackageSessionsQuery(Member $member, ?string $today = null): mixed
    {
        $today ??= now()->toDateString();

        return $member->packageSessions()
            ->with(['package', 'trainer'])
            ->where('status', 'active')
            ->where('remaining_sessions', '>', 0)
            ->where(function ($query) use ($today): void {
                $query->whereNull('expired_at')
                    ->orWhereDate('expired_at', '>=', $today);
            });
    }
}
