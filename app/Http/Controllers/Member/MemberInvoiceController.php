<?php

namespace App\Http\Controllers\Member;

use App\Features\Invoices\Queries\InvoiceDocumentQuery;
use App\Features\MemberPortal\Queries\MemberDashboardQuery;
use App\Http\Controllers\Controller;
use App\Models\Invoice;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class MemberInvoiceController extends Controller
{
    public function show(Request $request, Invoice $invoice, InvoiceDocumentQuery $invoiceQuery, MemberDashboardQuery $memberQuery): View
    {
        $this->authorize('view', $invoice);

        return view('invoices.show', [
            'layout' => 'member',
            'portal' => $memberQuery->forUser($request->user()),
            'document' => $invoiceQuery->forInvoice($invoice, 'Member'),
            'title' => 'Invoice Transaksi',
        ]);
    }
}
