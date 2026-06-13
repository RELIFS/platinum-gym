<?php

namespace App\Features\MemberPortal\Queries;

use App\Models\ClassEnrollment;
use App\Models\ClassSchedule;
use App\Models\Member;
use App\Models\Package;
use App\Models\QrToken;
use App\Models\User;
use Illuminate\Support\Collection;

class MemberDashboardQuery
{
    /**
     * @return array<string, mixed>
     */
    public function forUser(User $user): array
    {
        $member = $user->member()->firstOrFail();
        $today = now()->toDateString();

        $activeMembership = $member->memberships()
            ->with('package')
            ->where('status', 'active')
            ->whereDate('start_date', '<=', $today)
            ->whereDate('end_date', '>=', $today)
            ->orderBy('end_date')
            ->first();

        $latestMembership = $member->memberships()
            ->with('package')
            ->latest('end_date')
            ->latest('created_at')
            ->first();

        $activePackageSessions = $member->packageSessions()
            ->with(['package', 'trainer'])
            ->where('status', 'active')
            ->where('remaining_sessions', '>', 0)
            ->where(function ($query) use ($today): void {
                $query->whereNull('expired_at')
                    ->orWhereDate('expired_at', '>=', $today);
            })
            ->orderByDesc('remaining_sessions')
            ->limit(3)
            ->get();

        $activeSessionPackageIds = $member->packageSessions()
            ->where('status', 'active')
            ->where('remaining_sessions', '>', 0)
            ->where(function ($query) use ($today): void {
                $query->whereNull('expired_at')
                    ->orWhereDate('expired_at', '>=', $today);
            })
            ->pluck('package_id');

        $hiddenSessionPackageIds = $member->packageSessions()
            ->where(function ($query) use ($today): void {
                $query->where('remaining_sessions', '<=', 0)
                    ->orWhereDate('expired_at', '<', $today)
                    ->orWhereIn('status', ['expired', 'exhausted', 'cancelled', 'canceled']);
            })
            ->pluck('package_id')
            ->diff($activeSessionPackageIds)
            ->values();

        $paymentStatuses = ['pending', 'waiting_payment', 'waiting_confirmation', 'unpaid'];

        $pendingPaymentCount = $member->payments()
            ->whereIn('status', $paymentStatuses)
            ->count();

        $payments = $member->payments()
            ->with(['invoice', 'payable'])
            ->latest('created_at')
            ->limit(8)
            ->get();

        $upcomingEnrollments = $member->classEnrollments()
            ->with(['schedule.gymClass', 'schedule.trainer'])
            ->whereDate('session_date', '>=', $today)
            ->whereNotIn('status', ['cancelled', 'canceled'])
            ->orderBy('session_date')
            ->limit(4)
            ->get();

        $recentEnrollments = $member->classEnrollments()
            ->with(['schedule.gymClass', 'schedule.trainer'])
            ->latest('session_date')
            ->limit(8)
            ->get();

        $recentCheckIns = $member->gymCheckIns()
            ->latest('check_in_at')
            ->limit(4)
            ->get();

        $qrToken = QrToken::query()
            ->where('tokenable_type', Member::class)
            ->where('tokenable_id', $member->id)
            ->where('purpose', 'member')
            ->latest('created_at')
            ->first();

        $packages = Package::query()
            ->where('is_active', true)
            ->when($hiddenSessionPackageIds->isNotEmpty(), fn ($query) => $query->whereNotIn('id', $hiddenSessionPackageIds))
            ->orderByRaw("case when package_kind = 'membership' then 0 else 1 end")
            ->orderBy('price')
            ->limit(8)
            ->get();

        $classSchedules = ClassSchedule::query()
            ->with(['gymClass', 'trainer'])
            ->withCount(['enrollments as booked_count' => fn ($query) => $query->whereIn('status', ['booked', 'active', 'confirmed'])])
            ->where('is_active', true)
            ->whereHas('gymClass', fn ($query) => $query->where('is_active', true))
            ->orderBy('day_of_week')
            ->orderBy('start_time')
            ->limit(8)
            ->get();

        $notifications = $user->notifications()
            ->latest('created_at')
            ->limit(8)
            ->get();

        return [
            'user' => $user,
            'member' => $member,
            'activeMembership' => $activeMembership,
            'latestMembership' => $latestMembership,
            'activePackageSessions' => $activePackageSessions,
            'pendingPaymentCount' => $pendingPaymentCount,
            'payments' => $payments,
            'upcomingEnrollments' => $upcomingEnrollments,
            'recentEnrollments' => $recentEnrollments,
            'recentCheckIns' => $recentCheckIns,
            'qrToken' => $qrToken,
            'qrTokenIsActive' => $this->qrTokenIsActive($qrToken),
            'qrStatusLabel' => $this->qrStatusLabel($qrToken),
            'packages' => $packages,
            'classSchedules' => $classSchedules,
            'notifications' => $notifications,
            'unreadNotificationsCount' => $user->unreadNotifications()->count(),
            'stats' => $this->stats($member, $activeMembership, $pendingPaymentCount, $upcomingEnrollments),
            'activity' => $this->activity($latestMembership, $payments, $recentEnrollments, $recentCheckIns),
        ];
    }

    /**
     * @return array<int, array<string, string>>
     */
    private function stats(Member $member, mixed $activeMembership, int $pendingPaymentCount, Collection $upcomingEnrollments): array
    {
        return [
            [
                'label' => 'Status Akun',
                'value' => $this->statusLabel((string) $member->status),
                'description' => 'Member sejak '.$member->joined_at?->translatedFormat('d M Y'),
            ],
            [
                'label' => 'Membership',
                'value' => $activeMembership?->package?->name ?? 'Belum aktif',
                'description' => $activeMembership ? 'Aktif sampai '.$activeMembership->end_date?->translatedFormat('d M Y') : 'Paket aktif akan tampil setelah pembelian terverifikasi.',
            ],
            [
                'label' => 'Booking',
                'value' => (string) $upcomingEnrollments->count(),
                'description' => 'Jadwal kelas mendatang.',
            ],
            [
                'label' => 'Pembayaran',
                'value' => (string) $pendingPaymentCount,
                'description' => 'Pembayaran menunggu tindakan.',
            ],
        ];
    }

    private function statusLabel(string $status): string
    {
        return match ($status) {
            'active' => 'Aktif',
            'inactive' => 'Nonaktif',
            'suspended' => 'Ditangguhkan',
            default => str($status)->headline()->toString(),
        };
    }

    private function qrTokenIsActive(?QrToken $qrToken): bool
    {
        if (! $qrToken || $qrToken->is_revoked) {
            return false;
        }

        return is_null($qrToken->expires_at) || $qrToken->expires_at->isFuture();
    }

    private function qrStatusLabel(?QrToken $qrToken): string
    {
        if ($this->qrTokenIsActive($qrToken)) {
            return 'Aktif';
        }

        if ($qrToken?->is_revoked) {
            return 'Dicabut';
        }

        if ($qrToken?->expires_at?->isPast()) {
            return 'Kedaluwarsa';
        }

        return 'Belum diterbitkan';
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    private function activity(mixed $latestMembership, Collection $payments, Collection $enrollments, Collection $checkIns): Collection
    {
        $items = collect();

        if ($latestMembership) {
            $items->push([
                'type' => 'membership',
                'title' => 'Membership '.$this->statusLabel((string) $latestMembership->status),
                'description' => $latestMembership->package?->name ?? $latestMembership->code,
                'date' => $latestMembership->updated_at ?? $latestMembership->created_at,
            ]);
        }

        $payments->take(3)->each(function ($payment) use ($items): void {
            $items->push([
                'type' => 'payment',
                'title' => 'Pembayaran '.str((string) $payment->status)->headline()->toString(),
                'description' => $payment->payment_code.' - Rp '.number_format((float) $payment->amount, 0, ',', '.'),
                'date' => $payment->paid_at ?? $payment->created_at,
            ]);
        });

        $enrollments->take(3)->each(function (ClassEnrollment $enrollment) use ($items): void {
            $items->push([
                'type' => 'booking',
                'title' => 'Booking '.str((string) $enrollment->status)->headline()->toString(),
                'description' => $enrollment->schedule?->gymClass?->name ?? 'Kelas Platinum Gym',
                'date' => $enrollment->session_date,
            ]);
        });

        $checkIns->take(3)->each(function ($checkIn) use ($items): void {
            $items->push([
                'type' => 'check-in',
                'title' => 'Check-in Gym',
                'description' => 'Masuk '.($checkIn->check_in_at?->format('H:i') ?? '-'),
                'date' => $checkIn->check_in_at,
            ]);
        });

        return $items
            ->filter(fn (array $item): bool => filled($item['date']))
            ->sortByDesc('date')
            ->values()
            ->take(6);
    }
}
