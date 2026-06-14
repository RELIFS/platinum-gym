<?php

namespace App\Features\Payments\Contracts;

use App\Models\Payment;

interface PaymentGateway
{
    /**
     * @return array{token: string, redirect_url: string|null, raw: array<string, mixed>}
     */
    public function createSnapTransaction(Payment $payment): array;
}
