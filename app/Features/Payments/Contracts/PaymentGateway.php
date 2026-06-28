<?php

namespace App\Features\Payments\Contracts;

use App\Models\Payment;

interface PaymentGateway
{
    /**
     * @return array{token: string, redirect_url: string|null, raw: array<string, mixed>}
     */
    public function createSnapTransaction(Payment $payment): array;

    /**
     * Fetch the current Midtrans Core API status snapshot for the given payment.
     * Returns null when the payment has no Midtrans order id yet.
     * Throws \RuntimeException when the request fails for a reason other than
     * "not found" (Midtrans returns 404 when the order has not been processed yet).
     *
     * @return array<string, mixed>|null
     */
    public function fetchTransactionStatus(Payment $payment): ?array;
}
