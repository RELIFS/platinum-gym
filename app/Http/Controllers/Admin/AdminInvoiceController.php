<?php

namespace App\Http\Controllers\Admin;

use App\Features\Admin\Queries\AdminDashboardQuery;
use App\Features\Invoices\Actions\RenderInvoicePdfAction;
use App\Features\Invoices\Queries\InvoiceDocumentQuery;
use App\Http\Controllers\Controller;
use App\Models\Invoice;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminInvoiceController extends Controller
{
    public function show(Request $request, Invoice $invoice, InvoiceDocumentQuery $invoiceQuery, AdminDashboardQuery $adminQuery): View
    {
        $this->authorize('view', $invoice);

        return view('invoices.show', [
            'layout' => 'admin',
            'portal' => $adminQuery->forUser($request->user(), [], 'payments'),
            'navigation' => $adminQuery->navigation(),
            'document' => $this->document($invoiceQuery, $invoice),
            'title' => 'Invoice Transaksi',
        ]);
    }

    public function receipt(Request $request, Invoice $invoice, InvoiceDocumentQuery $invoiceQuery, AdminDashboardQuery $adminQuery): View
    {
        $this->authorize('view', $invoice);

        return view('invoices.receipt', [
            'layout' => 'admin',
            'portal' => $adminQuery->forUser($request->user(), [], 'payments'),
            'navigation' => $adminQuery->navigation(),
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
        return array_replace($invoiceQuery->forInvoice($invoice, 'Admin'), [
            'actions' => [
                'show' => route('admin.invoices.show', $invoice),
                'receipt' => route('admin.invoices.receipt', $invoice),
                'download' => route('admin.invoices.download', $invoice),
                'receipt_download' => route('admin.invoices.download', ['invoice' => $invoice, 'type' => 'receipt']),
            ],
        ]);
    }
}
