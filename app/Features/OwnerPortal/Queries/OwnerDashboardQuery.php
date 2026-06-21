<?php

namespace App\Features\OwnerPortal\Queries;

use App\Features\Reports\Data\ReportFilters;
use App\Features\Reports\Queries\OwnerReportQuery;
use App\Models\ClassEnrollment;
use App\Models\Member;
use App\Models\Membership;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class OwnerDashboardQuery
{
    public function __construct(private readonly OwnerReportQuery $reports) {}

    /** @return array<string, mixed> */
    public function forUser(User $user): array
    {
        $filters = new ReportFilters(
            from: now()->startOfMonth(),
            to: now()->startOfDay(),
            reportType: 'finance',
        );

        return [
            'owner' => $user,
            'filters' => $filters,
            'kpis' => $this->kpis($filters),
            'businessTrend' => $this->businessTrend(),
            'revenueByMethod' => $this->revenueByMethod($filters),
            'revenueByService' => $this->revenueByService($filters),
            'recentPayments' => $this->recentPayments(),
            'expiringMemberships' => $this->expiringMemberships(),
        ];
    }

    /** @return array<int, array<string, mixed>> */
    public function navigation(): array
    {
        return $this->reports->navigation();
    }

    /** @return array<int, array<string, string>> */
    private function kpis(ReportFilters $filters): array
    {
        $paidPayments = $this->reports->paidPaymentsQuery($filters);

        return [
            ['label' => 'Pendapatan periode ini', 'value' => $this->reports->money((clone $paidPayments)->sum('amount')), 'description' => 'Total transaksi lunas bulan berjalan.'],
            ['label' => 'Transaksi terkonfirmasi', 'value' => (string) (clone $paidPayments)->count(), 'description' => 'Pembayaran berstatus lunas.'],
            ['label' => 'Member aktif', 'value' => (string) Member::query()->where('status', 'active')->count(), 'description' => 'Member dengan status aktif.'],
            ['label' => 'Membership aktif', 'value' => (string) Membership::query()->where('status', 'active')->whereDate('end_date', '>=', now()->toDateString())->count(), 'description' => 'Membership yang masih berjalan.'],
            ['label' => 'Booking periode ini', 'value' => (string) ClassEnrollment::query()->whereBetween('session_date', [$filters->from->toDateString(), $filters->to->toDateString()])->whereNotIn('status', ['cancelled', 'canceled'])->count(), 'description' => 'Booking kelas bulan berjalan.'],
        ];
    }

    /** @return array<string, mixed> */
    private function businessTrend(): array
    {
        $end = now()->startOfDay();
        $start = $end->copy()->subDays(13);
        $filters = new ReportFilters($start, $end, 'finance');
        $dates = collect(range(0, 13))->map(fn (int $offset): Carbon => $start->copy()->addDays($offset));
        $dateKeys = $dates->mapWithKeys(fn (Carbon $date): array => [$date->toDateString() => ['revenue' => 0.0, 'transactions' => 0]]);

        $daily = $this->reports->paidPaymentsQuery($filters)
            ->get()
            ->reduce(function (Collection $carry, Payment $payment): Collection {
                $date = ($payment->paid_at ?? $payment->created_at)?->toDateString();
                if (! $date || ! $carry->has($date)) {
                    return $carry;
                }

                $current = $carry->get($date);
                $current['revenue'] += (float) $payment->amount;
                $current['transactions']++;
                $carry->put($date, $current);

                return $carry;
            }, $dateKeys);

        $revenue = $dates->map(fn (Carbon $date): int => (int) round($daily->get($date->toDateString())['revenue'] ?? 0))->all();
        $transactions = $dates->map(fn (Carbon $date): int => (int) ($daily->get($date->toDateString())['transactions'] ?? 0))->all();

        return [
            'title' => 'Tren pendapatan',
            'description' => 'Pendapatan terkonfirmasi dan jumlah transaksi lunas dalam 14 hari terakhir.',
            'period' => '14 hari terakhir',
            'labels' => $dates->map(fn (Carbon $date): string => $date->translatedFormat('d M'))->all(),
            'series' => [
                ['name' => 'Pendapatan', 'tone' => 'gold', 'color' => '#FEAC18', 'values' => $revenue, 'total' => array_sum($revenue), 'format' => 'money'],
                ['name' => 'Transaksi', 'tone' => 'sky', 'color' => '#38BDF8', 'values' => $transactions, 'total' => array_sum($transactions), 'format' => 'number'],
            ],
            'maxValue' => max(1, max($revenue), max($transactions)),
            'isEmpty' => array_sum($revenue) + array_sum($transactions) === 0,
        ];
    }

    private function revenueByMethod(ReportFilters $filters): Collection
    {
        return $this->reports->paidPaymentsQuery($filters)
            ->get()
            ->groupBy('method')
            ->map(fn (Collection $payments, string $method): array => [
                'label' => str($method)->headline()->toString(),
                'count' => $payments->count(),
                'total' => $this->reports->money($payments->sum('amount')),
            ])
            ->sortByDesc('count')
            ->values()
            ->take(4);
    }

    private function revenueByService(ReportFilters $filters): Collection
    {
        return $this->reports->paidPaymentsQuery($filters)
            ->get()
            ->groupBy(fn (Payment $payment): string => $this->reports->paymentServiceKind($payment))
            ->map(fn (Collection $payments, string $service): array => [
                'label' => $service,
                'count' => $payments->count(),
                'total' => $this->reports->money($payments->sum('amount')),
            ])
            ->sortByDesc('count')
            ->values()
            ->take(4);
    }

    private function recentPayments(): Collection
    {
        $filters = new ReportFilters(now()->subMonths(6)->startOfDay(), now()->startOfDay(), 'finance');

        return $this->reports->paidPaymentsQuery($filters)
            ->limit(6)
            ->get();
    }

    private function expiringMemberships(): Collection
    {
        return Membership::query()
            ->with(['member.user', 'package'])
            ->where('status', 'active')
            ->whereBetween('end_date', [now()->toDateString(), now()->addDays(14)->toDateString()])
            ->orderBy('end_date')
            ->limit(6)
            ->get();
    }
}
