<?php

namespace App\Features\CheckIns\Actions;

use App\Models\ClassEnrollment;
use App\Models\Member;
use App\Models\MemberPackageSession;
use App\Models\Membership;
use App\Models\QrToken;
use App\Support\MemberQrAccess;
use Illuminate\Support\Collection;
use RuntimeException;

class PreviewMemberQrCheckInAction
{
    private const NO_CONFIRMED_CLASS_BOOKING_NOTICE = 'Tidak ada booking kelas confirmed hari ini untuk paket sesi ini.';

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

        $activeSessions = MemberQrAccess::activePackageSessionsQuery($member, $today)
            ->orderByDesc('remaining_sessions')
            ->get();
        $sessionsForPreview = $membership
            ? $activeSessions
            : $activeSessions->filter(fn (MemberPackageSession $session): bool => MemberQrAccess::isStandaloneSessionType($session->package?->type))->values();

        if (! $membership && $sessionsForPreview->isEmpty()) {
            throw new RuntimeException('Membership atau paket sesi aktif tidak ditemukan.');
        }

        $previewSessions = $this->previewSessions($member, $sessionsForPreview, $today);
        $hasClassSessionCandidate = $sessionsForPreview->contains(fn (MemberPackageSession $session): bool => MemberQrAccess::isStandaloneSessionType($session->package?->type));
        $hasEligibleClassSession = $previewSessions->contains(fn (array $session): bool => filled($session['class_enrollment_id'] ?? null));

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
            'sessions' => $previewSessions->values()->all(),
            'session_notice' => $hasClassSessionCandidate && ! $hasEligibleClassSession ? self::NO_CONFIRMED_CLASS_BOOKING_NOTICE : null,
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

    /**
     * @param  Collection<int, MemberPackageSession>  $sessions
     * @return Collection<int, array<string, mixed>>
     */
    private function previewSessions(Member $member, Collection $sessions, string $today): Collection
    {
        return $sessions
            ->flatMap(function (MemberPackageSession $session) use ($member, $today): array {
                if (MemberQrAccess::isStandaloneSessionType($session->package?->type)) {
                    return $this->eligibleClassBookings($member, $session, $today)
                        ->map(fn ($enrollment): array => $this->formatClassSession($session, $enrollment))
                        ->all();
                }

                return [$this->formatPackageSession($session)];
            })
            ->values();
    }

    /**
     * @return Collection<int, ClassEnrollment>
     */
    private function eligibleClassBookings(Member $member, MemberPackageSession $session, string $today): Collection
    {
        $packageType = (string) $session->package?->type;

        return $member->classEnrollments()
            ->with(['schedule.gymClass', 'schedule.trainer', 'attendance'])
            ->where('status', 'confirmed')
            ->whereDate('session_date', $today)
            ->whereDoesntHave('attendance')
            ->whereDoesntHave('packageSessionUsages')
            ->whereHas('schedule', function ($query) use ($packageType, $session): void {
                $query->where('is_active', true)
                    ->when($session->trainer_id, fn ($query) => $query->where('trainer_id', $session->trainer_id))
                    ->whereHas('gymClass', function ($query) use ($packageType): void {
                        $query->where('is_active', true)
                            ->where(function ($query) use ($packageType): void {
                                $query->where('required_package_type', $packageType)
                                    ->orWhere(function ($query) use ($packageType): void {
                                        $query->whereNull('required_package_type')
                                            ->where('class_type', $packageType);
                                    });
                            });
                    });
            })
            ->get()
            ->filter(fn ($enrollment): bool => (int) $enrollment->schedule?->day_of_week === (int) $enrollment->session_date?->dayOfWeekIso)
            ->sortBy(fn ($enrollment): string => (string) $enrollment->schedule?->start_time)
            ->values();
    }

    /**
     * @return array<string, mixed>
     */
    private function formatPackageSession(MemberPackageSession $session): array
    {
        return [
            'id' => $session->id,
            'class_enrollment_id' => null,
            'name' => $session->package?->name ?? $session->code,
            'class_name' => null,
            'schedule_date' => null,
            'schedule_time' => null,
            'remaining' => (int) $session->remaining_sessions,
            'total' => (int) $session->total_sessions,
            'trainer' => $session->trainer?->name,
            'expired_at' => $session->expired_at?->translatedFormat('d M Y'),
            'usage_label' => $this->sessionLabel($session),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function formatClassSession(MemberPackageSession $session, ClassEnrollment $enrollment): array
    {
        $schedule = $enrollment->schedule;
        $gymClass = $schedule?->gymClass;
        $trainerName = $schedule?->trainer?->name ?? $session->trainer?->name;
        $timeRange = $this->timeRange($schedule?->start_time, $schedule?->end_time);
        $dateLabel = $enrollment->session_date?->translatedFormat('d M Y');

        return [
            'id' => $session->id,
            'class_enrollment_id' => $enrollment->id,
            'name' => $session->package?->name ?? $session->code,
            'class_name' => $gymClass?->name,
            'schedule_date' => $dateLabel,
            'schedule_time' => $timeRange,
            'remaining' => (int) $session->remaining_sessions,
            'total' => (int) $session->total_sessions,
            'trainer' => $trainerName,
            'expired_at' => $session->expired_at?->translatedFormat('d M Y'),
            'usage_label' => $this->sessionLabel($session, $gymClass?->name, $dateLabel, $timeRange, $trainerName),
        ];
    }

    private function sessionLabel(MemberPackageSession $session, ?string $className = null, ?string $date = null, ?string $time = null, ?string $trainer = null): string
    {
        $parts = array_filter([
            $className,
            $date,
            $time,
            $trainer,
            $session->package?->name ?? $session->code,
            ((int) $session->remaining_sessions).'/'.((int) $session->total_sessions).' sesi',
        ]);

        return implode(' - ', $parts);
    }

    private function timeRange(mixed $startTime, mixed $endTime): ?string
    {
        if (blank($startTime) || blank($endTime)) {
            return null;
        }

        return str((string) $startTime)->substr(0, 5)->toString().' - '.str((string) $endTime)->substr(0, 5)->toString();
    }
}
