<?php

namespace App\Features\Reports\Actions;

use Barryvdh\DomPDF\Facade\Pdf;
use Symfony\Component\HttpFoundation\Response;

class ExportReportPdfAction
{
    /** @param array<string, mixed> $payload */
    public function download(array $payload, string $filename): Response
    {
        return Pdf::loadView('reports.pdf.report', ['report' => $payload])
            ->setPaper('a4')
            ->download($filename);
    }
}
