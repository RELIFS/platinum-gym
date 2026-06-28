<?php

namespace App\Http\Controllers\Member;

use App\Features\Invoices\Actions\RenderInvoicePdfAction;
use App\Features\Invoices\Queries\InvoiceDocumentQuery;
use App\Features\MemberPortal\Queries\MemberDashboardQuery;
use App\Http\Controllers\Controller;
use App\Models\Invoice;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class MemberInvoiceController extends Controller
{
    public function show(Request $request, Invoice $invoice, InvoiceDocumentQuery $invoiceQuery, MemberDashboardQuery $memberQuery): View
    {
        $this->authorize('view', $invoice);

        return view('invoices.show', [
            'layout' => 'member',
            'portal' => $memberQuery->forUser($request->user()),
            'document' => $this->document($invoiceQuery, $invoice),
            'title' => 'Invoice Transaksi',
        ]);
    }

    public function receipt(Request $request, Invoice $invoice, InvoiceDocumentQuery $invoiceQuery, MemberDashboardQuery $memberQuery): View
    {
        $this->authorize('view', $invoice);

        return view('invoices.receipt', [
            'layout' => 'member',
            'portal' => $memberQuery->forUser($request->user()),
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
        return array_replace($invoiceQuery->forInvoice($invoice, 'Member'), [
            'actions' => [
                'show' => route('member.invoices.show', $invoice),
                'receipt' => route('member.invoices.receipt', $invoice),
                'download' => route('member.invoices.download', $invoice),
                'receipt_download' => route('member.invoices.download', ['invoice' => $invoice, 'type' => 'receipt']),
            ],
        ]);
    }
}
