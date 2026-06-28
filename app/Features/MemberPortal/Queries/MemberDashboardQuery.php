<?php

namespace App\Features\MemberPortal\Queries;

use App\Features\Bookings\Support\BookingTimePolicy;
use App\Features\Classes\Support\ClassStaffPresenter;
use App\Features\MemberPortal\Support\MemberPackageEligibility;
use App\Features\MemberPortal\ViewModels\MemberPortalStatusViewModel;
use App\Features\Payments\Actions\SyncMidtransPaymentStatusAction;
use App\Models\ClassEnrollment;
use App\Models\ClassSchedule;
use App\Models\GymCheckIn;
use App\Models\Member;
use App\Models\MemberPackageSessionUsage;
use App\Models\Package;
use App\Models\Payment;
use App\Models\QrToken;
use App\Models\Trainer;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Support\Collection;

class MemberDashboardQuery
{
    public function __construct(private readonly SyncMidtransPaymentStatusAction $syncMidtransStatus) {}

    /**
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    public function forUser(User $user, string $pageKey = 'dashboard', array $filters = []): array
    {
        $member = $user->member()->firstOrFail();
        $today = now()->toDateString();

        // Best-effort sync of any waiting Midtrans payment so the member sees
        // the freshest status without depending on webhook delivery.
        if (in_array($pageKey, ['dashboard', 'transaksi', 'membership'], true)) {
            $this->syncPendingMidtransPayments($member);
        }

        $startedMemberships = $member->memberships()
            ->with('package')
            ->startedAndCurrent($today)
            ->orderBy('end_date')
            ->orderBy('created_at')
            ->get();

        $awaitingMemberships = $member->memberships()
            ->with('package')
            ->awaitingFirstCheckIn()
            ->orderBy('activated_at')
            ->orderBy('created_at')
            ->get();

        $activeMemberships = $startedMemberships->concat($awaitingMemberships)->values();
        $activeMembership = $activeMemberships->first();

        $latestMembership = $member->memberships()
            ->with('package')
            ->latest('updated_at')
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

        $hiddenSessionPackageIds = $this->hiddenSessionPackageIds($member, $today);
        $pendingPaymentCount = $member->payments()
            ->whereIn('status', ['pending', 'waiting_payment', 'waiting_confirmation', 'unpaid'])
            ->count();

        $payments = $this->payments($member, $pageKey, $filters);

        $upcomingEnrollments = $member->classEnrollments()
            ->with(['schedule.gymClass', 'schedule.trainer'])
            ->whereDate('session_date', '>=', $today)
            ->whereNotIn('status', ['cancelled', 'canceled'])
            ->orderBy('session_date')
            ->limit(4)
            ->get()
            ->each(fn (ClassEnrollment $enrollment) => $enrollment->setAttribute('member_status_meta', MemberPortalStatusViewModel::booking($enrollment)));

        $recentEnrollments = $this->recentEnrollments($member, $pageKey, $filters);

        $recentCheckIns = $member->gymCheckIns()
            ->with(['membership.package', 'packageSessionUsages.packageSession.package'])
            ->latest('check_in_at')
            ->limit(8)
            ->get();

        $recentStandaloneSessionUsages = $member->packageSessionUsages()
            ->with(['packageSession.package'])
            ->whereNull('gym_check_in_id')
            ->latest('used_at')
            ->limit(8)
            ->get();

        $qrToken = QrToken::query()
            ->where('tokenable_type', Member::class)
            ->where('tokenable_id', $member->id)
            ->where('purpose', 'member')
            ->latest('created_at')
            ->first();

        $hasActiveGymMembership = $this->hasActiveGymMembership($member, $today);
        $packages = $this->packages($member, $hiddenSessionPackageIds, $pageKey, $filters, (bool) $activeMembership, $hasActiveGymMembership);
        $classSchedules = $this->classSchedules($member, $pageKey, $filters, $today);
        $notifications = $this->notifications($user, $pageKey, $filters);
        $trainerOptions = $this->trainerOptionsForPackages($packages);

        return [
            'user' => $user,
            'member' => $member,
            'activeMembership' => $activeMembership,
            'activeMemberships' => $activeMemberships,
            'latestMembership' => $latestMembership,
            'activePackageSessions' => $activePackageSessions,
            'pendingPaymentCount' => $pendingPaymentCount,
            'payments' => $payments,
            'upcomingEnrollments' => $upcomingEnrollments,
            'recentEnrollments' => $recentEnrollments,
            'recentCheckIns' => $recentCheckIns,
            'recentCheckInRows' => $this->recentCheckInRows($recentCheckIns, $recentStandaloneSessionUsages),
            'qrToken' => $qrToken,
            'qrTokenIsActive' => $this->qrTokenIsActive($qrToken, $activeMembership),
            'qrStatusLabel' => $this->qrStatusLabel($qrToken, $activeMembership),
            'packages' => $packages,
            'packageGroups' => $this->packageGroups($packages),
            'hasActiveGymMembership' => $hasActiveGymMembership,
            'classSchedules' => $classSchedules,
            'classScheduleGroups' => $this->classScheduleGroups($classSchedules),
            'notifications' => $notifications,
            'trainerOptions' => $trainerOptions,
            'unreadNotificationsCount' => $user->unreadNotifications()->count(),
            'pageFilters' => $this->pageFilters($filters),
            'filterOptions' => $this->filterOptions(),
            'stats' => $this->stats($member, $activeMembership, $pendingPaymentCount, $upcomingEnrollments),
            'activity' => $this->activity($latestMembership, $this->dashboardCollection($payments), $this->dashboardCollection($recentEnrollments), $recentCheckIns),
        ];
    }

    private function nextSessionDate(int $dayOfWeek): CarbonImmutable
    {
        $date = BookingTimePolicy::earliestBookingDate();

        while ($date->dayOfWeekIso !== $dayOfWeek) {
            $date = $date->addDay();
        }

        return $date;
    }

    /**
     * @param  Collection<int, GymCheckIn>  $recentCheckIns
     * @param  Collection<int, MemberPackageSessionUsage>  $recentStandaloneSessionUsages
     * @return Collection<int, array{date_label: string, time_label: string, package_label: string, status_label: string, status_class: string, remaining_label: string}>
     */
    private function recentCheckInRows(Collection $recentCheckIns, Collection $recentStandaloneSessionUsages): Collection
    {
        return $recentCheckIns
            ->map(fn (GymCheckIn $checkIn): array => $this->recentCheckInRow($checkIn))
            ->concat($recentStandaloneSessionUsages->map(fn (MemberPackageSessionUsage $usage): array => $this->recentSessionUsageRow($usage)))
            ->sortByDesc('sort_at')
            ->take(8)
            ->map(function (array $row): array {
                unset($row['sort_at']);

                return $row;
            })
            ->values();
    }

    /**
     * @return array{date_label: string, time_label: string, package_label: string, status_label: string, status_class: string, remaining_label: string, sort_at: int}
     */
    private function recentCheckInRow(GymCheckIn $checkIn): array
    {
        $sessionUsages = $checkIn->packageSessionUsages;
        $hasSessionUsage = $sessionUsages->isNotEmpty();
        $membershipLabel = $checkIn->membership?->package?->name ?? 'Membership aktif';
        $sessionLabels = $sessionUsages
            ->map(fn (MemberPackageSessionUsage $usage): ?string => $usage->packageSession?->package?->name ?? $usage->packageSession?->code)
            ->filter()
            ->unique()
            ->values();
        $remainingLabels = $sessionUsages
            ->map(fn (MemberPackageSessionUsage $usage): ?string => $usage->packageSession?->remaining_sessions !== null ? ((int) $usage->packageSession->remaining_sessions).' sesi' : null)
            ->filter()
            ->unique()
            ->values();

        return [
            'date_label' => $checkIn->check_in_date?->translatedFormat('d M Y') ?? '-',
            'time_label' => $checkIn->check_in_at?->format('H:i') ?? '-',
            'package_label' => $hasSessionUsage ? collect([$membershipLabel])->merge($sessionLabels)->filter()->unique()->implode(' + ') : $membershipLabel,
            'status_label' => $hasSessionUsage ? 'Check-in + Sesi' : 'Check-in',
            'status_class' => $hasSessionUsage ? 'member-status-info' : 'member-status-success',
            'remaining_label' => $remainingLabels->isNotEmpty() ? $remainingLabels->implode(', ') : '-',
            'sort_at' => $checkIn->check_in_at?->getTimestamp() ?? 0,
        ];
    }

    /**
     * @return array{date_label: string, time_label: string, package_label: string, status_label: string, status_class: string, remaining_label: string, sort_at: int}
     */
    private function recentSessionUsageRow(MemberPackageSessionUsage $usage): array
    {
        return [
            'date_label' => $usage->usage_date?->translatedFormat('d M Y') ?? '-',
            'time_label' => $usage->used_at?->format('H:i') ?? '-',
            'package_label' => $usage->packageSession?->package?->name ?? $usage->packageSession?->code ?? 'Paket sesi',
            'status_label' => 'Sesi',
            'status_class' => 'member-status-info',
            'remaining_label' => $usage->packageSession?->remaining_sessions !== null ? ((int) $usage->packageSession->remaining_sessions).' sesi' : '-',
            'sort_at' => $usage->used_at?->getTimestamp() ?? 0,
        ];
    }

    /**
     * Lazily sync up to a small number of pending Midtrans payments so the member
     * sees the freshest status. Errors from the gateway are intentionally swallowed
     * (logged via report()) inside the action so a slow Midtrans call cannot break
     * the page.
     */
    private function syncPendingMidtransPayments(Member $member): void
    {
        $pending = $member->payments()
            ->where('method', 'midtrans')
            ->whereIn('status', ['waiting_payment', 'pending', 'unpaid'])
            ->whereNotNull('midtrans_order_id')
            ->orderByDesc('created_at')
            ->limit(3)
            ->get();

        foreach ($pending as $payment) {
            $this->syncMidtransStatus->handle($payment);
        }
    }

    /**
     * @return Collection<int, int>
     */
    private function hiddenSessionPackageIds(Member $member, string $today): Collection
    {
        $activeSessionPackageIds = $member->packageSessions()
            ->where('status', 'active')
            ->where('remaining_sessions', '>', 0)
            ->where(function ($query) use ($today): void {
                $query->whereNull('expired_at')
                    ->orWhereDate('expired_at', '>=', $today);
            })
            ->pluck('package_id');

        return $member->packageSessions()
            ->where(function ($query) use ($today): void {
                $query->where('remaining_sessions', '<=', 0)
                    ->orWhereDate('expired_at', '<', $today)
                    ->orWhereIn('status', ['expired', 'exhausted', 'cancelled', 'canceled']);
            })
            ->pluck('package_id')
            ->diff($activeSessionPackageIds)
            ->values();
    }

    private function payments(Member $member, string $pageKey, array $filters): mixed
    {
        $query = $member->payments()
            ->with(['invoice', 'payable'])
            ->latest('created_at');

        if ($pageKey === 'transaksi') {
            $this->applyPaymentFilters($query, $filters);

            return $query->paginate(8)->withQueryString()->through(function (Payment $payment): Payment {
                $payment->setAttribute('member_status_meta', MemberPortalStatusViewModel::payment($payment));

                return $payment;
            });
        }

        return $query->limit(8)->get()->each(function (Payment $payment): void {
            $payment->setAttribute('member_status_meta', MemberPortalStatusViewModel::payment($payment));
        });
    }

    private function recentEnrollments(Member $member, string $pageKey, array $filters): mixed
    {
        $query = $member->classEnrollments()
            ->with(['schedule.gymClass', 'schedule.trainer'])
            ->latest('session_date')
            ->latest('created_at');

        if ($pageKey === 'riwayat-booking') {
            $this->applyBookingFilters($query, $filters);

            return $query->paginate(8)->withQueryString()->through(function (ClassEnrollment $enrollment): ClassEnrollment {
                $enrollment->setAttribute('member_status_meta', MemberPortalStatusViewModel::booking($enrollment));

                return $enrollment;
            });
        }

        return $query->limit(8)->get()->each(function (ClassEnrollment $enrollment): void {
            $enrollment->setAttribute('member_status_meta', MemberPortalStatusViewModel::booking($enrollment));
        });
    }

    private function hasActiveGymMembership(Member $member, string $today): bool
    {
        return $member->memberships()
            ->activeForAccess($today)
            ->whereHas('package', fn ($query) => $query->whereIn('type', ['gym', 'include']))
            ->exists();
    }

    private function packages(Member $member, Collection $hiddenSessionPackageIds, string $pageKey, array $filters, bool $hasActiveMembership, bool $hasActiveGymMembership): mixed
    {
        $query = Package::query()
            ->where('is_active', true)
            ->when($hiddenSessionPackageIds->isNotEmpty(), fn ($query) => $query->whereNotIn('id', $hiddenSessionPackageIds))
            ->orderByRaw("case when package_kind = 'membership' then 0 else 1 end")
            ->orderByRaw("case when category = 'umum' then 0 when category = 'mahasiswa' then 1 else 2 end")
            ->orderByRaw("case when type = 'gym' then 0 when type = 'senam' then 1 when type = 'include' then 2 when type = 'muaythai' then 3 when type = 'poundfit' then 4 when type = 'pt' then 5 else 6 end")
            ->orderBy('price')
            ->orderBy('name');

        if ($pageKey === 'membership') {
            $this->applyPackageFilters($query, $filters);

            return $query->paginate(24)->withQueryString()->through(function (Package $package) use ($member, $hasActiveMembership, $hasActiveGymMembership): Package {
                $package->setAttribute('member_status_meta', MemberPortalStatusViewModel::package($package));
                $package->setAttribute('member_eligibility', MemberPackageEligibility::forPackage(
                    $member,
                    $package,
                    $hasActiveMembership,
                    $hasActiveGymMembership,
                    route('member.profile.edit'),
                    route('member.membership', ['kind' => 'membership', 'q' => 'Gym'])
                ));

                return $package;
            });
        }

        return $query->limit(8)->get()->each(function (Package $package) use ($member, $hasActiveMembership, $hasActiveGymMembership): void {
            $package->setAttribute('member_status_meta', MemberPortalStatusViewModel::package($package));
            $package->setAttribute('member_eligibility', MemberPackageEligibility::forPackage(
                $member,
                $package,
                $hasActiveMembership,
                $hasActiveGymMembership,
                route('member.profile.edit'),
                route('member.membership', ['kind' => 'membership', 'q' => 'Gym'])
            ));
        });
    }

    /**
     * @return array<int, array{key: string, title: string, packages: Collection<int, Package>}>
     */
    private function packageGroups(mixed $packages): array
    {
        $items = $packages instanceof Paginator
            ? collect($packages->items())
            : ($packages instanceof Collection ? $packages : collect());

        $groups = [
            ['key' => 'umum', 'title' => 'Kategori Umum', 'packages' => $items->filter(fn (Package $package): bool => $this->packageBelongsToGroup($package, 'umum'))->values()],
            ['key' => 'mahasiswa', 'title' => 'Kategori Mahasiswa', 'packages' => $items->filter(fn (Package $package): bool => $this->packageBelongsToGroup($package, 'mahasiswa'))->values()],
            ['key' => 'poundfit', 'title' => 'Poundfit', 'packages' => $items->filter(fn (Package $package): bool => (string) $package->type === 'poundfit')->values()],
            ['key' => 'personal-trainer', 'title' => 'Personal Trainer', 'packages' => $items->filter(fn (Package $package): bool => (string) $package->type === 'pt')->values()],
            ['key' => 'lainnya', 'title' => 'Layanan Lainnya', 'packages' => $items->filter(fn (Package $package): bool => ! $this->packageHasNamedGroup($package))->values()],
        ];

        return collect($groups)
            ->filter(fn (array $group): bool => $group['packages']->isNotEmpty())
            ->values()
            ->all();
    }

    private function packageBelongsToGroup(Package $package, string $category): bool
    {
        $type = (string) $package->type;

        if (! in_array($type, ['gym', 'senam', 'include', 'muaythai'], true)) {
            return false;
        }

        if ($category === 'mahasiswa') {
            return str((string) $package->category)->lower()->toString() === 'mahasiswa' || filled($package->max_age);
        }

        return str((string) $package->category)->lower()->toString() === 'umum' && blank($package->max_age);
    }

    private function packageHasNamedGroup(Package $package): bool
    {
        return $this->packageBelongsToGroup($package, 'umum')
            || $this->packageBelongsToGroup($package, 'mahasiswa')
            || (string) $package->type === 'poundfit'
            || (string) $package->type === 'pt';
    }

    private function classSchedules(Member $member, string $pageKey, array $filters, string $today): mixed
    {
        $activeSessionTypes = $this->activePackageSessionTypes($member, $today);
        $activeMuaythaiTrainerIds = $this->activePackageSessionTrainerIds($member, 'muaythai', $today);

        $query = ClassSchedule::query()
            ->with(['gymClass', 'trainer'])
            ->withCount(['enrollments as booked_count' => fn ($query) => $query->whereIn('status', ['booked', 'active', 'confirmed', 'pending_payment'])])
            ->where('is_active', true)
            ->whereHas('gymClass', fn ($query) => $query->where('is_active', true))
            ->orderBy('day_of_week')
            ->orderBy('start_time');

        if ($activeMuaythaiTrainerIds->isNotEmpty()) {
            $query->where(function ($query) use ($activeMuaythaiTrainerIds): void {
                $query->whereDoesntHave('gymClass', function ($query): void {
                    $query->where(function ($query): void {
                        $query->where('class_type', 'muaythai')
                            ->orWhere('name', 'like', '%Muaythai%');
                    });
                })->orWhereIn('trainer_id', $activeMuaythaiTrainerIds->all());
            });
        }

        if ($pageKey === 'booking-kelas') {
            $this->applyScheduleFilters($query, $filters);

            return $query->paginate(24)->withQueryString()->through(fn (ClassSchedule $schedule) => $this->prepareSchedule($schedule, $activeSessionTypes, $activeMuaythaiTrainerIds));
        }

        return $query->limit(8)->get()->each(fn (ClassSchedule $schedule) => $this->prepareSchedule($schedule, $activeSessionTypes, $activeMuaythaiTrainerIds));
    }

    private function notifications(User $user, string $pageKey, array $filters): mixed
    {
        $query = $user->notifications()->latest('created_at');

        if ($pageKey === 'notifikasi') {
            $status = $this->filterString($filters, 'status');

            $query->when($status === 'baru', fn ($query) => $query->whereNull('read_at'))
                ->when($status === 'dibaca', fn ($query) => $query->whereNotNull('read_at'));

            return $query->paginate(8)->withQueryString()->through(function ($notification) {
                $notification->setAttribute('member_status_meta', MemberPortalStatusViewModel::notification($notification));

                return $notification;
            });
        }

        return $query->limit(8)->get()->each(function ($notification): void {
            $notification->setAttribute('member_status_meta', MemberPortalStatusViewModel::notification($notification));
        });
    }

    /**
     * @return Collection<int, string>
     */
    private function activePackageSessionTypes(Member $member, string $today): Collection
    {
        return $member->packageSessions()
            ->with('package:id,type')
            ->where('status', 'active')
            ->where('remaining_sessions', '>', 0)
            ->where(function ($query) use ($today): void {
                $query->whereNull('expired_at')
                    ->orWhereDate('expired_at', '>=', $today);
            })
            ->get()
            ->pluck('package.type')
            ->filter()
            ->unique()
            ->values();
    }

    /**
     * @return Collection<int, int>
     */
    private function activePackageSessionTrainerIds(Member $member, string $packageType, string $today): Collection
    {
        return $member->packageSessions()
            ->with('package:id,type')
            ->where('status', 'active')
            ->where('remaining_sessions', '>', 0)
            ->whereNotNull('trainer_id')
            ->where(function ($query) use ($today): void {
                $query->whereNull('expired_at')
                    ->orWhereDate('expired_at', '>=', $today);
            })
            ->whereHas('package', fn ($query) => $query->where('type', $packageType))
            ->pluck('trainer_id')
            ->filter()
            ->map(fn (mixed $id): int => (int) $id)
            ->unique()
            ->values();
    }

    /**
     * @param  Collection<int, string>  $activeSessionTypes
     * @param  Collection<int, int>  $activeMuaythaiTrainerIds
     */
    private function prepareSchedule(ClassSchedule $schedule, Collection $activeSessionTypes, Collection $activeMuaythaiTrainerIds): ClassSchedule
    {
        $schedule->setAttribute('next_session_date', $this->nextSessionDate((int) $schedule->day_of_week)->toDateString());
        $schedule->setAttribute('staff_role_label', ClassStaffPresenter::roleLabel($schedule));
        $schedule->setAttribute('staff_display_name', ClassStaffPresenter::memberBookingDisplayName($schedule->trainer, $schedule));
        $schedule->setAttribute('time_label', ClassStaffPresenter::timeLabel($schedule));
        $meta = MemberPortalStatusViewModel::schedule($schedule);
        $requiredPackageType = (string) $schedule->gymClass?->required_package_type;

        if (
            ($meta['is_session_based'] ?? false)
            && filled($requiredPackageType)
            && $requiredPackageType !== 'poundfit'
            && ! $activeSessionTypes->contains($requiredPackageType)
        ) {
            $meta['can_book'] = false;
            $meta['disabled_reason'] = 'Kelas ini membutuhkan membership aktif yang sesuai.';
        }

        if (
            $requiredPackageType === 'muaythai'
            && $activeMuaythaiTrainerIds->isNotEmpty()
            && ! $activeMuaythaiTrainerIds->contains((int) $schedule->trainer_id)
        ) {
            $meta['can_book'] = false;
            $meta['disabled_reason'] = 'Jadwal Muaythai mengikuti coach pada paket aktif Anda.';
        }

        $schedule->setAttribute('member_status_meta', $meta);

        return $schedule;
    }

    /**
     * @return array<int, array{key: string, title: string, schedules: Collection<int, ClassSchedule>}>
     */
    private function classScheduleGroups(mixed $classSchedules): array
    {
        $items = $classSchedules instanceof Paginator
            ? collect($classSchedules->items())
            : ($classSchedules instanceof Collection ? $classSchedules : collect());

        $groups = collect([
            ['key' => 'aerobic', 'title' => 'Aerobic'],
            ['key' => 'zumba', 'title' => 'Zumba'],
            ['key' => 'muaythai', 'title' => 'Muaythai'],
            ['key' => 'poundfit', 'title' => 'Poundfit'],
            ['key' => 'lainnya', 'title' => 'Kelas Lainnya'],
        ])->map(function (array $group) use ($items): array {
            $group['schedules'] = $items
                ->filter(fn (ClassSchedule $schedule): bool => $this->scheduleGroupKey($schedule) === $group['key'])
                ->values();

            return $group;
        });

        return $groups
            ->filter(fn (array $group): bool => $group['schedules']->isNotEmpty())
            ->values()
            ->all();
    }

    private function scheduleGroupKey(ClassSchedule $schedule): string
    {
        $className = str((string) $schedule->gymClass?->name)->lower()->toString();
        $classType = str((string) $schedule->gymClass?->class_type)->lower()->toString();

        return match (true) {
            str_contains($className, 'aerobic') => 'aerobic',
            str_contains($className, 'zumba') => 'zumba',
            str_contains($className, 'muaythai') || $classType === 'muaythai' => 'muaythai',
            str_contains($className, 'poundfit') || $classType === 'poundfit' => 'poundfit',
            default => 'lainnya',
        };
    }

    /**
     * Build trainer dropdown options keyed by package id, only for packages
     * that require trainer selection (PT, Muaythai). Membership and gym packages
     * skip the dropdown.
     *
     * @return array<int, array<int, array{id: int, label: string}>>
     */
    private function trainerOptionsForPackages(mixed $packages): array
    {
        $packageItems = $packages instanceof Paginator
            ? collect($packages->items())
            : ($packages instanceof Collection ? $packages : collect());

        $needsTrainer = $packageItems->filter(function (Package $package): bool {
            $meta = $package->member_status_meta ?? [];

            return (bool) ($meta['requires_trainer'] ?? false);
        });

        if ($needsTrainer->isEmpty()) {
            return [];
        }

        $specializations = $needsTrainer
            ->map(fn (Package $package) => $package->member_status_meta['trainer_specialization'] ?? null)
            ->filter()
            ->unique()
            ->values();

        if ($specializations->isEmpty()) {
            return [];
        }

        $trainersBySpec = Trainer::query()
            ->where('is_active', true)
            ->whereIn('specialization', $specializations->all())
            ->orderBy('name')
            ->get(['id', 'name', 'specialization'])
            ->groupBy('specialization');

        $options = [];

        foreach ($needsTrainer as $package) {
            $spec = $package->member_status_meta['trainer_specialization'] ?? null;

            if (! $spec) {
                continue;
            }

            $matches = $trainersBySpec->get($spec, collect());

            $options[(int) $package->id] = $matches
                ->map(fn (Trainer $trainer) => ['id' => (int) $trainer->id, 'label' => (string) $trainer->name])
                ->values()
                ->all();
        }

        return $options;
    }

    private function applyPaymentFilters(mixed $query, array $filters): void
    {
        $search = $this->filterString($filters, 'q');
        $status = $this->filterString($filters, 'status');

        $query->when($search, function ($query, string $search): void {
            $query->where(function ($query) use ($search): void {
                $query->where('payment_code', 'like', '%'.$search.'%')
                    ->orWhere('method', 'like', '%'.$search.'%')
                    ->orWhere('status', 'like', '%'.$search.'%')
                    ->orWhereHas('invoice', fn ($query) => $query->where('invoice_number', 'like', '%'.$search.'%'));
            });
        });

        $query->when($status, fn ($query, string $status) => $query->where('status', $status));
    }

    private function applyBookingFilters(mixed $query, array $filters): void
    {
        $search = $this->filterString($filters, 'q');
        $status = $this->filterString($filters, 'status');

        $query->when($search, function ($query, string $search): void {
            $query->where(function ($query) use ($search): void {
                $query->where('status', 'like', '%'.$search.'%')
                    ->orWhereHas('schedule.gymClass', fn ($query) => $query->where('name', 'like', '%'.$search.'%'))
                    ->orWhereHas('schedule.trainer', fn ($query) => $query->where('name', 'like', '%'.$search.'%'));
            });
        });

        $query->when($status, fn ($query, string $status) => $query->where('status', $status));
    }

    private function applyPackageFilters(mixed $query, array $filters): void
    {
        $search = $this->filterString($filters, 'q');
        $kind = $this->filterString($filters, 'kind');

        $query->when($search, function ($query, string $search): void {
            $query->where(function ($query) use ($search): void {
                $query->where('name', 'like', '%'.$search.'%')
                    ->orWhere('description', 'like', '%'.$search.'%')
                    ->orWhere('package_kind', 'like', '%'.$search.'%')
                    ->orWhere('type', 'like', '%'.$search.'%')
                    ->orWhere('category', 'like', '%'.$search.'%');
            });
        });

        $query->when($kind, fn ($query, string $kind) => $query->where('package_kind', $kind));
    }

    private function applyScheduleFilters(mixed $query, array $filters): void
    {
        $search = $this->filterString($filters, 'q');
        $day = $this->filterString($filters, 'day');
        $access = $this->filterString($filters, 'access');

        $query->when($search, function ($query, string $search): void {
            $query->where(function ($query) use ($search): void {
                $query->where('room', 'like', '%'.$search.'%')
                    ->orWhereHas('gymClass', function ($query) use ($search): void {
                        $query->where('name', 'like', '%'.$search.'%')
                            ->orWhere('class_type', 'like', '%'.$search.'%')
                            ->orWhere('access_type', 'like', '%'.$search.'%');
                    })
                    ->orWhereHas('trainer', fn ($query) => $query->where('name', 'like', '%'.$search.'%'));
            });
        });

        $query->when(in_array((int) $day, range(1, 7), true), fn ($query) => $query->where('day_of_week', (int) $day));
        $query->when($access, fn ($query, string $access) => $query->whereHas('gymClass', fn ($query) => $query->where('access_type', $access)));
    }

    /**
     * @return array<string, string|null>
     */
    private function pageFilters(array $filters): array
    {
        return [
            'q' => $this->filterString($filters, 'q'),
            'status' => $this->filterString($filters, 'status'),
            'kind' => $this->filterString($filters, 'kind'),
            'day' => $this->filterString($filters, 'day'),
            'access' => $this->filterString($filters, 'access'),
        ];
    }

    /**
     * @return array<string, array<string|int, string>>
     */
    private function filterOptions(): array
    {
        return [
            'paymentStatuses' => MemberPortalStatusViewModel::paymentStatusOptions(),
            'bookingStatuses' => MemberPortalStatusViewModel::bookingStatusOptions(),
            'notificationStatuses' => MemberPortalStatusViewModel::notificationStatusOptions(),
            'packageKinds' => MemberPortalStatusViewModel::packageKindOptions(),
            'days' => MemberPortalStatusViewModel::dayOptions(),
            'classAccess' => MemberPortalStatusViewModel::classAccessOptions(),
        ];
    }

    private function filterString(array $filters, string $key): ?string
    {
        $value = $filters[$key] ?? null;

        if (is_array($value)) {
            return null;
        }

        $value = trim((string) $value);

        return $value === '' ? null : mb_substr($value, 0, 120);
    }

    private function dashboardCollection(mixed $items): Collection
    {
        return $items instanceof Collection ? $items : collect($items->items());
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
                'description' => $activeMembership ? $activeMembership->validityLabel() : 'Paket aktif akan tampil setelah pembelian terverifikasi.',
            ],
            [
                'label' => 'Booking',
                'value' => (string) $upcomingEnrollments->count(),
                'description' => 'Jadwal kelas mendatang.',
            ],
            [
                'label' => 'Pembayaran',
                'value' => (string) $pendingPaymentCount,
                'description' => 'Pembayaran yang perlu dicek.',
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

    private function qrTokenIsActive(?QrToken $qrToken, mixed $activeMembership): bool
    {
        if (! $qrToken || $qrToken->is_revoked) {
            return false;
        }

        return filled($activeMembership);
    }

    private function qrStatusLabel(?QrToken $qrToken, mixed $activeMembership): string
    {
        if ($this->qrTokenIsActive($qrToken, $activeMembership)) {
            return 'Aktif';
        }

        if ($qrToken?->is_revoked) {
            return 'Dicabut';
        }

        if ($qrToken) {
            return 'Belum aktif';
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
            $meta = $payment->member_status_meta ?? MemberPortalStatusViewModel::payment($payment);

            $items->push([
                'type' => 'payment',
                'title' => 'Pembayaran '.$meta['label'],
                'description' => $payment->payment_code.' - Rp '.number_format((float) $payment->amount, 0, ',', '.'),
                'date' => $payment->paid_at ?? $payment->created_at,
            ]);
        });

        $enrollments->take(3)->each(function (ClassEnrollment $enrollment) use ($items): void {
            $meta = $enrollment->member_status_meta ?? MemberPortalStatusViewModel::booking($enrollment);

            $items->push([
                'type' => 'booking',
                'title' => 'Booking '.$meta['label'],
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
