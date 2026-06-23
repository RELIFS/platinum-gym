<?php

namespace App\Features\Reports\Exports;

use App\Features\Reports\Data\ReportFilters;
use App\Features\Reports\Queries\OwnerReportQuery;
use Generator;
use Maatwebsite\Excel\Concerns\FromGenerator;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class OwnerReportExport implements FromGenerator, ShouldAutoSize, WithHeadings, WithTitle
{
    public function __construct(
        private readonly OwnerReportQuery $query,
        private readonly ReportFilters $filters,
    ) {}

    public function generator(): Generator
    {
        yield from $this->query->exportRowsGenerator($this->filters);
    }

    /** @return array<int, string> */
    public function headings(): array
    {
        return $this->query->headings($this->filters);
    }

    public function title(): string
    {
        return str((string) (ReportFilters::TYPES[$this->filters->reportType] ?? 'Laporan'))->limit(31, '')->toString();
    }
}
