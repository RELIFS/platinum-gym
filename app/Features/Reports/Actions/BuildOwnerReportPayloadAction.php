<?php

namespace App\Features\Reports\Actions;

use App\Features\Reports\Data\ReportFilters;
use App\Features\Reports\Queries\OwnerReportQuery;
use App\Models\Setting;
use App\Models\User;

class BuildOwnerReportPayloadAction
{
    public function __construct(private readonly OwnerReportQuery $query) {}

    /** @return array<string, mixed> */
    public function handle(ReportFilters $filters, User $generatedBy): array
    {
        return [
            'title' => 'Laporan '.(ReportFilters::TYPES[$filters->reportType] ?? 'Owner'),
            'subtitle' => $this->description($filters->reportType),
            'period' => $filters->periodLabel(),
            'filters' => $filters->query(),
            'generatedAt' => now()->translatedFormat('d M Y H:i'),
            'generatedBy' => $generatedBy->name,
            'summary' => $this->query->summary($filters),
            'headings' => $this->query->headings($filters),
            'rows' => $this->query->exportRows($filters)->values()->all(),
            'totals' => [],
            'business' => $this->business(),
        ];
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

    private function description(string $reportType): string
    {
        return match ($reportType) {
            'members' => 'Ringkasan member, membership, dan masa aktif pada periode laporan.',
            'classes' => 'Ringkasan booking kelas dan status peserta pada periode laporan.',
            default => 'Ringkasan pendapatan dan transaksi lunas pada periode laporan.',
        };
    }
}
