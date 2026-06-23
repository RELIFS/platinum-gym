<?php

namespace App\Http\Controllers\Owner;

use App\Features\Invoices\Actions\RenderInvoicePdfAction;
use App\Features\Invoices\Queries\InvoiceDocumentQuery;
use App\Features\OwnerPortal\Queries\OwnerDashboardQuery;
use App\Http\Controllers\Controller;
use App\Models\Invoice;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class OwnerInvoiceController extends Controller
{
    public function show(Request $request, Invoice $invoice, InvoiceDocumentQuery $invoiceQuery, OwnerDashboardQuery $ownerQuery): View
    {
        $this->authorize('view', $invoice);

        return view('invoices.show', [
            'layout' => 'owner',
            'portal' => ['owner' => $request->user()],
            'navigation' => $ownerQuery->navigation(),
            'document' => $this->document($invoiceQuery, $invoice),
            'title' => 'Invoice Transaksi',
        ]);
    }

    public function receipt(Request $request, Invoice $invoice, InvoiceDocumentQuery $invoiceQuery, OwnerDashboardQuery $ownerQuery): View
    {
        $this->authorize('view', $invoice);

        return view('invoices.receipt', [
            'layout' => 'owner',
            'portal' => ['owner' => $request->user()],
            'navigation' => $ownerQuery->navigation(),
            'document' => $this->document($invoiceQuery, $invoice),
            'title' => 'Struk Transaksi',
        ]);
    }

    public function download(Invoice $invoice, InvoiceDocumentQuery $invoiceQuery, RenderInvoicePdfAction $pdf): Response
    {
        $this->authorize('download', $invoice);

        $type = request()->query('type') === 'receipt' ? 'receipt' : 'invoice';

        return $pdf->download($this->document($invoiceQuery, $invoice), $type);
    }

    /** @return array<string, mixed> */
    private function document(InvoiceDocumentQuery $invoiceQuery, Invoice $invoice): array
    {
        return array_replace($invoiceQuery->forInvoice($invoice, 'Owner'), [
            'actions' => [
                'show' => route('owner.invoices.show', $invoice),
                'receipt' => route('owner.invoices.receipt', $invoice),
                'download' => route('owner.invoices.download', $invoice),
                'receipt_download' => route('owner.invoices.download', ['invoice' => $invoice, 'type' => 'receipt']),
            ],
        ]);
    }
}
