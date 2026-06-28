<?php

namespace App\Features\Invoices\Actions;

use Barryvdh\DomPDF\Facade\Pdf;
use Symfony\Component\HttpFoundation\Response;

class RenderInvoicePdfAction
{
    /** @param array<string, mixed> $document */
    public function download(array $document, string $type = 'invoice'): Response
    {
        $invoice = $document['invoice'];
        $filename = str((string) $invoice->invoice_number)
            ->slug('-')
            ->prepend($type === 'receipt' ? 'struk-' : 'invoice-')
            ->append('.pdf')
            ->toString();

        $view = $type === 'receipt' ? 'invoices.receipt-pdf' : 'invoices.pdf';
        $pdf = Pdf::loadView($view, ['document' => $document]);

        if ($type === 'receipt') {
            $pdf->setPaper([0, 0, 226.77, 841.89]);
        } else {
            $pdf->setPaper('a4');
        }

        return $pdf->download($filename);
    }
}
