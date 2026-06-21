<?php

namespace App\Http\Controllers\Owner;

use App\Features\Invoices\Queries\InvoiceDocumentQuery;
use App\Features\OwnerPortal\Queries\OwnerDashboardQuery;
use App\Http\Controllers\Controller;
use App\Models\Invoice;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class OwnerInvoiceController extends Controller
{
    public function show(Request $request, Invoice $invoice, InvoiceDocumentQuery $invoiceQuery, OwnerDashboardQuery $ownerQuery): View
    {
        $this->authorize('view', $invoice);

        return view('invoices.show', [
            'layout' => 'owner',
            'portal' => ['owner' => $request->user()],
            'navigation' => $ownerQuery->navigation(),
            'document' => $invoiceQuery->forInvoice($invoice, 'Owner'),
            'title' => 'Invoice Transaksi',
        ]);
    }
}
