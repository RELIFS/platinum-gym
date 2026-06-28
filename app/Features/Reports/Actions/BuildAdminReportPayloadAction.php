<?php

namespace App\Features\Reports\Actions;

use App\Features\Admin\Queries\AdminDashboardQuery;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Support\Carbon;

class BuildAdminReportPayloadAction
{
    public function __construct(private readonly AdminDashboardQuery $query) {}

    /** @param array<string, mixed> $filters */
    public function handle(array $filters, User $generatedBy): array
    {
        [$from, $to] = $this->dateRange($filters);

        return [
            'title' => 'Laporan Operasional Admin',
            'subtitle' => 'Ringkasan operasional gym berdasarkan periode yang dipilih.',
            'period' => $from->translatedFormat('d M Y').' - '.$to->translatedFormat('d M Y'),
            'filters' => [
                'date_from' => $from->toDateString(),
                'date_to' => $to->toDateString(),
            ],
            'generatedAt' => now()->translatedFormat('d M Y H:i'),
            'generatedBy' => $generatedBy->name,
            'summary' => [],
            'headings' => ['Metrik', 'Nilai', 'Catatan'],
            'rows' => $this->query->reportRowsFor($filters)->values()->all(),
            'totals' => [],
            'business' => $this->business(),
        ];
    }

    /** @param array<string, mixed> $filters */
    private function dateRange(array $filters): array
    {
        $from = $this->parseDate($filters['date_from'] ?? null) ?? now()->startOfMonth();
        $to = $this->parseDate($filters['date_to'] ?? null) ?? now();

        return $from->greaterThan($to) ? [$to, $from] : [$from, $to];
    }

    private function parseDate(mixed $value): ?Carbon
    {
        if (! filled($value)) {
            return null;
        }

        try {
            return Carbon::parse($value)->startOfDay();
        } catch (\Throwable) {
            return null;
        }
    }

    /** @return array<string, string> */
    private function business(): array
    {
        $settings = Setting::query()
            ->whereIn('key', ['site_name', 'address', 'phone_display', 'public_email'])
            ->pluck('value', 'key');

        return [
            'site_name' => $settings->get('site_name', 'Platinum Gym Padang'),
            'address' => $settings->get('address', 'Padang, Sumatera Barat'),
            'phone_display' => $settings->get('phone_display', '+62 821-7477-7761'),
            'public_email' => $settings->get('public_email', 'info@platinumgympadang.com'),
        ];
    }
}
