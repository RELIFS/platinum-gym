<?php

namespace App\Http\Controllers\Owner;

use App\Features\Reports\Actions\BuildOwnerReportPayloadAction;
use App\Features\Reports\Actions\ExportReportPdfAction;
use App\Features\Reports\Data\ReportFilters;
use App\Features\Reports\Exports\OwnerReportExport;
use App\Features\Reports\Queries\OwnerReportQuery;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class OwnerReportController extends Controller
{
    public function index(Request $request, OwnerReportQuery $query): View
    {
        return $this->render($request, $query, 'finance', 'Laporan Owner');
    }

    public function finance(Request $request, OwnerReportQuery $query): View
    {
        return $this->render($request, $query, 'finance', 'Laporan Keuangan');
    }

    public function members(Request $request, OwnerReportQuery $query): View
    {
        return $this->render($request, $query, 'members', 'Laporan Member & Membership');
    }

    public function classes(Request $request, OwnerReportQuery $query): View
    {
        return $this->render($request, $query, 'classes', 'Laporan Booking & Kelas');
    }

    public function export(
        Request $request,
        OwnerReportQuery $query,
        BuildOwnerReportPayloadAction $payload,
        ExportReportPdfAction $pdf,
    ): Response {
        abort_unless($request->user()?->can('export_financial_reports'), 403);

        $filters = ReportFilters::fromRequest($request, (string) $request->query('report_type', 'finance'));
        $format = str((string) $request->query('format', 'csv'))->lower()->toString();
        $filename = 'laporan-owner-platinum-gym-'.$filters->reportType.'-'.now()->format('Ymd-His');

        if ($format === 'xlsx') {
            return Excel::download(new OwnerReportExport($query, $filters), $filename.'.xlsx');
        }

        if ($format === 'pdf') {
            return $pdf->download($payload->handle($filters, $request->user()), $filename.'.pdf');
        }

        return $this->csv($query, $filters, $filename.'.csv');
    }

    private function csv(OwnerReportQuery $query, ReportFilters $filters, string $filename): StreamedResponse
    {
        return response()->streamDownload(function () use ($query, $filters): void {
            $stream = fopen('php://output', 'w');
            fputcsv($stream, $query->headings($filters));

            foreach ($query->exportRowsGenerator($filters) as $row) {
                fputcsv($stream, $row);
            }

            fclose($stream);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    private function render(Request $request, OwnerReportQuery $query, string $defaultType, string $title): View
    {
        abort_unless($request->user()?->can('view_financial_reports'), 403);

        $filters = ReportFilters::fromRequest($request, $defaultType);

        return view('owner.reports.index', [
            'portal' => [
                'owner' => $request->user(),
                'filters' => $filters,
                'report' => $query->forFilters($filters),
            ],
            'navigation' => $query->navigation(),
            'title' => $title,
        ]);
    }
}
