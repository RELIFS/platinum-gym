<?php

namespace App\Features\Reports\Queries;

use App\Features\Reports\Data\ReportFilters;
use App\Models\ClassEnrollment;
use App\Models\GymClass;
use App\Models\Member;
use App\Models\MemberPackageSession;
use App\Models\Membership;
use App\Models\Package;
use App\Models\Payment;
use Generator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class OwnerReportQuery
{
    private const PER_PAGE = 12;

    /** @return array<int, array<string, mixed>> */
    public function navigation(): array
    {
        return [
            ['label' => 'Bisnis', 'items' => [
                ['label' => 'Dashboard', 'route' => 'owner.dashboard', 'active' => 'owner.dashboard', 'icon' => 'dashboard'],
                ['label' => 'Pusat Laporan', 'route' => 'owner.reports.index', 'active' => 'owner.reports.index', 'icon' => 'chart'],
            ]],
            ['label' => 'Laporan', 'items' => [
                ['label' => 'Keuangan', 'route' => 'owner.reports.finance', 'active' => ['owner.reports.finance', 'owner.invoices.*'], 'icon' => 'receipt'],
                ['label' => 'Member & Membership', 'route' => 'owner.reports.members', 'active' => 'owner.reports.members', 'icon' => 'members'],
                ['label' => 'Booking & Kelas', 'route' => 'owner.reports.classes', 'active' => 'owner.reports.classes', 'icon' => 'calendar'],
            ]],
            ['label' => 'Akun', 'items' => [
                ['label' => 'Profil', 'route' => 'profile.edit', 'active' => 'profile.*', 'icon' => 'user'],
            ]],
        ];
    }

    /** @return array<string, mixed> */
    public function forFilters(ReportFilters $filters): array
    {
        return [
            'title' => ReportFilters::TYPES[$filters->reportType] ?? 'Laporan',
            'description' => $this->description($filters->reportType),
            'summary' => $this->summary($filters),
            'rows' => $this->paginatedRows($filters),
            'headings' => $this->headings($filters),
            'options' => $this->options(),
        ];
    }

    /** @return array<int, string> */
    public function headings(ReportFilters $filters): array
    {
        return match ($filters->reportType) {
            'members' => ['Member', 'Kode', 'Paket', 'Periode', 'Status'],
            'classes' => ['Tanggal', 'Kelas', 'Member', 'Jam', 'Status'],
            default => ['Tanggal Bayar', 'Kode Pembayaran', 'Invoice', 'Member', 'Layanan', 'Metode', 'Nominal'],
        };
    }

    public function exportRows(ReportFilters $filters): Collection
    {
        return collect(iterator_to_array($this->exportRowsGenerator($filters), false));
    }

    /** @return Generator<int, array<int, string>> */
    public function exportRowsGenerator(ReportFilters $filters): Generator
    {
        if ($filters->reportType === 'members') {
            yield from $this->membershipRowsGenerator($filters);

            return;
        }

        if ($filters->reportType === 'classes') {
            yield from $this->classRowsGenerator($filters);

            return;
        }

        yield from $this->financeRowsGenerator($filters);
    }

    public function paidPaymentsQuery(ReportFilters $filters): Builder
    {
        $query = Payment::query()
            ->with(['member.user', 'invoice'])
            ->with(['payable' => function (MorphTo $morphTo): void {
                $morphTo->morphWith([
                    Membership::class => ['package'],
                    MemberPackageSession::class => ['package', 'trainer'],
                    ClassEnrollment::class => ['schedule.gymClass'],
                ]);
            }])
            ->where('status', 'paid');

        $this->applyPaidDateRange($query, $filters);

        if (filled($filters->method)) {
            $query->where('method', $filters->method);
        }

        if ($filters->packageId) {
            $this->applyPackageFilter($query, $filters->packageId);
        }

        if (filled($filters->search)) {
            $like = $this->like($filters->search);
            $query->where(fn (Builder $query) => $query
                ->where('payment_code', 'like', $like)
                ->orWhere('method', 'like', $like)
                ->orWhereHas('invoice', fn (Builder $invoiceQuery) => $invoiceQuery->where('invoice_number', 'like', $like))
                ->orWhereHas('member', fn (Builder $memberQuery) => $memberQuery
                    ->where('member_code', 'like', $like)
                    ->orWhereHas('user', fn (Builder $userQuery) => $userQuery->where('name', 'like', $like)->orWhere('email', 'like', $like))));
        }

        return $query->latest('paid_at')->latest('created_at');
    }

    /** @return array<int, array<string, string>> */
    public function summary(ReportFilters $filters): array
    {
        $payments = $this->paidPaymentsQuery($filters);

        return match ($filters->reportType) {
            'members' => [
                ['label' => 'Member baru', 'value' => (string) Member::query()->whereBetween('joined_at', [$filters->from->toDateString(), $filters->to->toDateString()])->count(), 'description' => 'Member yang bergabung pada periode ini.'],
                ['label' => 'Member aktif', 'value' => (string) Member::query()->where('status', 'active')->count(), 'description' => 'Total member aktif saat laporan dibuka.'],
                ['label' => 'Membership aktif', 'value' => (string) Membership::query()->where('status', 'active')->whereDate('end_date', '>=', now()->toDateString())->count(), 'description' => 'Membership yang masih aktif.'],
                ['label' => 'Akan berakhir', 'value' => (string) Membership::query()->where('status', 'active')->whereBetween('end_date', [now()->toDateString(), now()->addDays(14)->toDateString()])->count(), 'description' => 'Membership aktif yang berakhir dalam 14 hari.'],
            ],
            'classes' => [
                ['label' => 'Booking periode ini', 'value' => (string) ClassEnrollment::query()->whereBetween('session_date', [$filters->from->toDateString(), $filters->to->toDateString()])->whereNotIn('status', ['cancelled', 'canceled'])->count(), 'description' => 'Booking kelas dalam periode laporan.'],
                ['label' => 'Terkonfirmasi', 'value' => (string) ClassEnrollment::query()->whereBetween('session_date', [$filters->from->toDateString(), $filters->to->toDateString()])->where('status', 'confirmed')->count(), 'description' => 'Booking yang sudah dikonfirmasi.'],
                ['label' => 'Hadir', 'value' => (string) ClassEnrollment::query()->whereBetween('session_date', [$filters->from->toDateString(), $filters->to->toDateString()])->where('status', 'attended')->count(), 'description' => 'Peserta yang tercatat hadir.'],
                ['label' => 'Kelas aktif', 'value' => (string) GymClass::query()->where('is_active', true)->count(), 'description' => 'Kelas yang tersedia di sistem.'],
            ],
            default => [
                ['label' => 'Pendapatan periode ini', 'value' => $this->money((clone $payments)->sum('amount')), 'description' => 'Hanya transaksi dengan status lunas.'],
                ['label' => 'Transaksi terkonfirmasi', 'value' => (string) (clone $payments)->count(), 'description' => 'Jumlah pembayaran lunas pada periode ini.'],
                ['label' => 'Rata-rata transaksi', 'value' => $this->money((clone $payments)->avg('amount') ?? 0), 'description' => 'Nilai rata-rata transaksi lunas.'],
                ['label' => 'Metode pembayaran', 'value' => (string) (clone $payments)->distinct('method')->count('method'), 'description' => 'Metode yang dipakai pada periode ini.'],
            ],
        };
    }

    public function paymentServiceName(Payment $payment): string
    {
        $payable = $payment->payable;

        return $payable?->package?->name
            ?? $payable?->schedule?->gymClass?->name
            ?? 'Layanan Platinum Gym';
    }

    public function paymentServiceKind(Payment $payment): string
    {
        return match (true) {
            $payment->payable instanceof Membership => 'Membership',
            $payment->payable instanceof MemberPackageSession => 'Paket Sesi',
            $payment->payable instanceof ClassEnrollment => 'Booking Kelas',
            default => 'Layanan',
        };
    }

    public function statusLabel(?string $status): string
    {
        return match ((string) $status) {
            'active' => 'Aktif',
            'inactive' => 'Nonaktif',
            'paid' => 'Lunas',
            'waiting_confirmation' => 'Menunggu Konfirmasi',
            'waiting_payment', 'pending', 'unpaid', 'pending_payment' => 'Menunggu Pembayaran',
            'rejected' => 'Ditolak',
            'failed' => 'Gagal',
            'expired' => 'Kedaluwarsa',
            'booked' => 'Terdaftar',
            'confirmed' => 'Terkonfirmasi',
            'attended' => 'Hadir',
            'cancelled', 'canceled' => 'Dibatalkan',
            default => filled($status) ? str($status)->replace(['_', '-'], ' ')->headline()->toString() : '-',
        };
    }

    public function money(mixed $value): string
    {
        return 'Rp '.number_format((float) $value, 0, ',', '.');
    }

    private function paginatedRows(ReportFilters $filters): LengthAwarePaginator
    {
        return match ($filters->reportType) {
            'members' => $this->membershipRows($filters, true),
            'classes' => $this->classRows($filters, true),
            default => $this->financeRows($filters, true),
        };
    }

    private function financeRows(ReportFilters $filters, bool $paginate): Collection|LengthAwarePaginator
    {
        $query = $this->paidPaymentsQuery($filters);
        $mapper = fn (Payment $payment): array => $this->financeRow($payment);

        if ($paginate) {
            return $query->paginate(self::PER_PAGE)->withQueryString()->through($mapper);
        }

        return collect($this->financeRowsGenerator($filters));
    }

    /** @return array<int|string, string|null> */
    private function financeRow(Payment $payment, bool $withInvoiceUrl = true): array
    {
        $row = [
            $payment->paid_at?->translatedFormat('d M Y') ?? $payment->created_at?->translatedFormat('d M Y') ?? '-',
            $payment->payment_code,
            $payment->invoice?->invoice_number ?? '-',
            $payment->member?->user?->name ?? $payment->member?->member_code ?? '-',
            $this->paymentServiceName($payment),
            str((string) $payment->method)->headline()->toString(),
            $this->money($payment->amount),
        ];

        if ($withInvoiceUrl) {
            $row['invoice_url'] = $payment->invoice ? route('owner.invoices.show', $payment->invoice) : null;
        }

        return $row;
    }

    /** @return Generator<int, array<int, string>> */
    private function financeRowsGenerator(ReportFilters $filters): Generator
    {
        foreach ($this->paidPaymentsQuery($filters)->reorder()->lazyById(500) as $payment) {
            yield $this->financeRow($payment, false);
        }
    }

    private function membershipRows(ReportFilters $filters, bool $paginate): Collection|LengthAwarePaginator
    {
        $query = $this->membershipRowsQuery($filters);
        $mapper = fn (Membership $membership): array => $this->membershipRow($membership);

        if ($paginate) {
            return $query->paginate(self::PER_PAGE)->withQueryString()->through($mapper);
        }

        return collect($this->membershipRowsGenerator($filters));
    }

    /** @return array<int, string> */
    private function membershipRow(Membership $membership): array
    {
        return [
            $membership->member?->user?->name ?? $membership->member?->member_code ?? '-',
            $membership->code,
            $membership->package?->name ?? 'Membership',
            ($membership->start_date?->translatedFormat('d M Y') ?? '-').' - '.($membership->end_date?->translatedFormat('d M Y') ?? '-'),
            $this->statusLabel($membership->status),
        ];
    }

    /** @return Generator<int, array<int, string>> */
    private function membershipRowsGenerator(ReportFilters $filters): Generator
    {
        $query = $this->membershipRowsQuery($filters);

        foreach ($query->reorder()->lazyById(500) as $membership) {
            yield $this->membershipRow($membership);
        }
    }

    private function classRows(ReportFilters $filters, bool $paginate): Collection|LengthAwarePaginator
    {
        $query = $this->classRowsQuery($filters);
        $mapper = fn (ClassEnrollment $enrollment): array => $this->classRow($enrollment);

        if ($paginate) {
            return $query->paginate(self::PER_PAGE)->withQueryString()->through($mapper);
        }

        return collect($this->classRowsGenerator($filters));
    }

    /** @return array<int, string> */
    private function classRow(ClassEnrollment $enrollment): array
    {
        return [
            $enrollment->session_date?->translatedFormat('d M Y') ?? '-',
            $enrollment->schedule?->gymClass?->name ?? 'Kelas Platinum Gym',
            $enrollment->member?->user?->name ?? $enrollment->member?->member_code ?? '-',
            substr((string) $enrollment->schedule?->start_time, 0, 5),
            $this->statusLabel($enrollment->status),
        ];
    }

    /** @return Generator<int, array<int, string>> */
    private function classRowsGenerator(ReportFilters $filters): Generator
    {
        $query = $this->classRowsQuery($filters);

        foreach ($query->reorder()->lazyById(500) as $enrollment) {
            yield $this->classRow($enrollment);
        }
    }

    private function membershipRowsQuery(ReportFilters $filters): Builder
    {
        $query = Membership::query()
            ->with(['member.user', 'package'])
            ->whereDate('start_date', '<=', $filters->to->toDateString())
            ->whereDate('end_date', '>=', $filters->from->toDateString())
            ->latest('created_at');

        if (filled($filters->status)) {
            $query->where('status', $filters->status);
        }

        if ($filters->packageId) {
            $query->where('package_id', $filters->packageId);
        }

        if (filled($filters->search)) {
            $like = $this->like($filters->search);
            $query->where(fn (Builder $query) => $query
                ->where('code', 'like', $like)
                ->orWhere('status', 'like', $like)
                ->orWhereHas('package', fn (Builder $packageQuery) => $packageQuery->where('name', 'like', $like))
                ->orWhereHas('member', fn (Builder $memberQuery) => $memberQuery
                    ->where('member_code', 'like', $like)
                    ->orWhereHas('user', fn (Builder $userQuery) => $userQuery->where('name', 'like', $like)->orWhere('email', 'like', $like))));
        }

        return $query;
    }

    private function classRowsQuery(ReportFilters $filters): Builder
    {
        $query = ClassEnrollment::query()
            ->with(['member.user', 'schedule.gymClass', 'schedule.trainer'])
            ->whereBetween('session_date', [$filters->from->toDateString(), $filters->to->toDateString()])
            ->latest('session_date')
            ->latest('created_at');

        if (filled($filters->status)) {
            $query->where('status', $filters->status);
        }

        if ($filters->classId) {
            $query->whereHas('schedule', fn (Builder $scheduleQuery) => $scheduleQuery->where('gym_class_id', $filters->classId));
        }

        if (filled($filters->search)) {
            $like = $this->like($filters->search);
            $query->where(fn (Builder $query) => $query
                ->where('status', 'like', $like)
                ->orWhereHas('member', fn (Builder $memberQuery) => $memberQuery->where('member_code', 'like', $like)->orWhereHas('user', fn (Builder $userQuery) => $userQuery->where('name', 'like', $like)))
                ->orWhereHas('schedule.gymClass', fn (Builder $classQuery) => $classQuery->where('name', 'like', $like)));
        }

        return $query;
    }

    private function applyPaidDateRange(Builder $query, ReportFilters $filters): void
    {
        $from = $filters->from->copy()->startOfDay();
        $to = $filters->to->copy()->endOfDay();

        $query->where(function (Builder $query) use ($from, $to): void {
            $query->whereBetween('paid_at', [$from, $to])
                ->orWhere(fn (Builder $fallbackQuery) => $fallbackQuery
                    ->whereNull('paid_at')
                    ->whereBetween('created_at', [$from, $to]));
        });
    }

    private function applyPackageFilter(Builder $query, int $packageId): void
    {
        $query->where(function (Builder $query) use ($packageId): void {
            $query
                ->whereHasMorph('payable', [Membership::class], fn (Builder $payableQuery) => $payableQuery->where('package_id', $packageId))
                ->orWhereHasMorph('payable', [MemberPackageSession::class], fn (Builder $payableQuery) => $payableQuery->where('package_id', $packageId));
        });
    }

    /** @return array<string, mixed> */
    private function options(): array
    {
        return [
            'reportTypes' => ReportFilters::TYPES,
            'paymentMethods' => Payment::query()->select('method')->whereNotNull('method')->distinct()->orderBy('method')->pluck('method', 'method')->map(fn (string $method): string => str($method)->headline()->toString())->all(),
            'packages' => Package::query()->orderBy('name')->pluck('name', 'id')->all(),
            'classes' => GymClass::query()->orderBy('name')->pluck('name', 'id')->all(),
            'statuses' => [
                'active' => 'Aktif',
                'booked' => 'Terdaftar',
                'confirmed' => 'Terkonfirmasi',
                'attended' => 'Hadir',
                'cancelled' => 'Dibatalkan',
            ],
        ];
    }

    private function description(string $reportType): string
    {
        return match ($reportType) {
            'members' => 'Pantau member aktif, membership berjalan, dan masa aktif yang perlu diperhatikan.',
            'classes' => 'Pantau booking kelas, status peserta, dan aktivitas kelas dalam periode laporan.',
            default => 'Pantau pendapatan terkonfirmasi dan transaksi lunas berdasarkan periode yang dipilih.',
        };
    }

    private function like(string $value): string
    {
        return '%'.$value.'%';
    }
}
