<?php

namespace App\Features\Payments\Gateways;

use App\Features\Payments\Contracts\PaymentGateway;
use App\Models\Payment;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class MidtransSnapGateway implements PaymentGateway
{
    /**
     * @return array{token: string, redirect_url: string|null, raw: array<string, mixed>}
     */
    public function createSnapTransaction(Payment $payment): array
    {
        $serverKey = (string) config('services.midtrans.server_key');

        if (blank($serverKey)) {
            throw new RuntimeException('Konfigurasi Midtrans belum lengkap.');
        }

        $payment->loadMissing(['member.user', 'payable']);
        $user = $payment->member?->user;

        $response = Http::baseUrl($this->baseUrl())
            ->timeout((int) config('services.midtrans.timeout', 10))
            ->withBasicAuth($serverKey, '')
            ->acceptJson()
            ->asJson()
            ->post('/transactions', [
                'transaction_details' => [
                    'order_id' => $payment->midtrans_order_id,
                    'gross_amount' => (int) round((float) $payment->amount),
                ],
                'customer_details' => [
                    'first_name' => $user?->name ?? $payment->member?->member_code ?? 'Member Platinum Gym',
                    'email' => $user?->email,
                    'phone' => $user?->phone,
                ],
                'item_details' => [[
                    'id' => $payment->payment_code,
                    'price' => (int) round((float) $payment->amount),
                    'quantity' => 1,
                    'name' => str($this->payableName($payment))->limit(48)->toString(),
                ]],
                'callbacks' => [
                    'finish' => route('member.transactions.show', $payment),
                    'unfinish' => route('member.transactions.show', $payment),
                    'error' => route('member.transactions.show', $payment),
                ],
            ]);

        if (! $response->successful()) {
            throw new RuntimeException('Midtrans belum dapat membuat transaksi pembayaran.');
        }

        $payload = $response->json();

        if (! is_array($payload) || blank($payload['token'] ?? null)) {
            throw new RuntimeException('Respons Midtrans tidak berisi token pembayaran.');
        }

        return [
            'token' => (string) $payload['token'],
            'redirect_url' => filled($payload['redirect_url'] ?? null) ? (string) $payload['redirect_url'] : null,
            'raw' => $payload,
        ];
    }

    private function baseUrl(): string
    {
        return config('services.midtrans.is_production')
            ? (string) config('services.midtrans.snap_production_url')
            : (string) config('services.midtrans.snap_sandbox_url');
    }

    private function apiBaseUrl(): string
    {
        return config('services.midtrans.is_production')
            ? (string) config('services.midtrans.api_production_url')
            : (string) config('services.midtrans.api_sandbox_url');
    }

    public function fetchTransactionStatus(Payment $payment): ?array
    {
        if (blank($payment->midtrans_order_id)) {
            return null;
        }

        $serverKey = (string) config('services.midtrans.server_key');

        if (blank($serverKey)) {
            throw new RuntimeException('Konfigurasi Midtrans belum lengkap.');
        }

        $response = Http::baseUrl($this->apiBaseUrl())
            ->timeout((int) config('services.midtrans.timeout', 10))
            ->withBasicAuth($serverKey, '')
            ->acceptJson()
            ->get('/v2/'.$payment->midtrans_order_id.'/status');

        if ($response->status() === 404) {
            // Order id not yet processed by Midtrans (member opened pay link but cancelled).
            return null;
        }

        if (! $response->successful()) {
            throw new RuntimeException('Midtrans belum dapat mengirim status transaksi.');
        }

        $payload = $response->json();

        if (! is_array($payload)) {
            throw new RuntimeException('Respons status Midtrans tidak valid.');
        }

        return $payload;
    }

    private function payableName(Payment $payment): string
    {
        $payable = $payment->payable;

        return $payable?->package?->name
            ?? $payable?->schedule?->gymClass?->name
            ?? $payment->payment_code;
    }
}
