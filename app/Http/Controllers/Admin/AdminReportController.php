<?php

namespace App\Http\Controllers\Admin;

use App\Features\Admin\Queries\AdminDashboardQuery;
use App\Features\Reports\Actions\BuildAdminReportPayloadAction;
use App\Features\Reports\Actions\ExportReportPdfAction;
use App\Features\Reports\Exports\AdminOperationalReportExport;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminReportController extends Controller
{
    public function export(
        Request $request,
        AdminDashboardQuery $query,
        BuildAdminReportPayloadAction $payload,
        ExportReportPdfAction $pdf,
    ): Response {
        abort_unless($request->user()?->can('export_operational_reports'), 403);

        $filters = $request->only(['date_from', 'date_to']);
        $format = str((string) $request->query('format', 'csv'))->lower()->toString();
        $filename = 'laporan-operasional-platinum-gym-'.now()->format('Ymd-His');

        if ($format === 'xlsx') {
            return Excel::download(new AdminOperationalReportExport($query, $filters), $filename.'.xlsx');
        }

        if ($format === 'pdf') {
            return $pdf->download($payload->handle($filters, $request->user()), $filename.'.pdf');
        }

        return $this->csv($query, $filters, $filename.'.csv');
    }

    /** @param array<string, mixed> $filters */
    private function csv(AdminDashboardQuery $query, array $filters, string $filename): StreamedResponse
    {
        return response()->streamDownload(function () use ($query, $filters): void {
            $stream = fopen('php://output', 'w');
            fputcsv($stream, ['Metrik', 'Nilai', 'Catatan']);

            foreach ($query->reportRowsFor($filters) as $row) {
                fputcsv($stream, $row);
            }

            fclose($stream);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
