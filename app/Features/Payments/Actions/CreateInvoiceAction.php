<?php

namespace App\Features\Payments\Actions;

use App\Features\Payments\Support\PaymentCode;
use App\Models\Invoice;
use App\Models\Payment;

class CreateInvoiceAction
{
    public function handle(Payment $payment): Invoice
    {
        return $payment->invoice()->firstOrCreate([], [
            'invoice_number' => PaymentCode::invoice(),
            'issued_at' => now()->toDateString(),
            'due_date' => $payment->expires_at?->toDateString(),
            'subtotal' => $payment->amount,
            'discount' => 0,
            'tax' => 0,
            'total' => $payment->amount,
            'status' => $payment->status === 'paid' ? 'paid' : 'issued',
            'meta' => [
                'payment_code' => $payment->payment_code,
                'method' => $payment->method,
            ],
        ]);
    }
}
