<?php

namespace App\Features\Reports\Exports;

use App\Features\Admin\Queries\AdminDashboardQuery;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class AdminOperationalReportExport implements FromArray, ShouldAutoSize, WithHeadings, WithTitle
{
    /** @param array<string, mixed> $filters */
    public function __construct(
        private readonly AdminDashboardQuery $query,
        private readonly array $filters,
    ) {}

    /** @return array<int, array<int, string>> */
    public function array(): array
    {
        return $this->query->reportRowsFor($this->filters)->values()->all();
    }

    /** @return array<int, string> */
    public function headings(): array
    {
        return ['Metrik', 'Nilai', 'Catatan'];
    }

    public function title(): string
    {
        return 'Operasional';
    }
}
