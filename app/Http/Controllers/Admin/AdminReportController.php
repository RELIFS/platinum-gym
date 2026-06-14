<?php

namespace App\Http\Controllers\Admin;

use App\Features\Admin\Queries\AdminDashboardQuery;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminReportController extends Controller
{
    public function export(Request $request, AdminDashboardQuery $query): StreamedResponse
    {
        abort_unless($request->user()?->can('export_operational_reports'), 403);

        $filters = $request->only(['date_from', 'date_to']);
        $filename = 'laporan-operasional-platinum-gym-'.now()->format('Ymd-His').'.csv';

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
