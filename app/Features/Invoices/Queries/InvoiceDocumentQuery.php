<?php

namespace App\Features\Invoices\Queries;

use App\Features\Reports\Queries\OwnerReportQuery;
use App\Models\ClassEnrollment;
use App\Models\Invoice;
use App\Models\MemberPackageSession;
use App\Models\Membership;
use App\Models\Setting;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class InvoiceDocumentQuery
{
    public function __construct(private readonly OwnerReportQuery $reports) {}

    /** @return array<string, mixed> */
    public function forInvoice(Invoice $invoice, string $viewerRole): array
    {
        $invoice->load([
            'payment.member.user',
            'payment.payable' => function (MorphTo $morphTo): void {
                $morphTo->morphWith([
                    Membership::class => ['package'],
                    MemberPackageSession::class => ['package', 'trainer'],
                    ClassEnrollment::class => ['schedule.gymClass', 'schedule.trainer'],
                ]);
            },
        ]);

        $payment = $invoice->payment;
        $settings = $this->settings();

        return [
            'viewerRole' => $viewerRole,
            'invoice' => $invoice,
            'payment' => $payment,
            'business' => $settings,
            'member' => [
                'name' => $payment?->member?->user?->name ?? '-',
                'code' => $payment?->member?->member_code ?? '-',
                'email' => $payment?->member?->user?->email ?? null,
            ],
            'service' => [
                'kind' => $payment ? $this->reports->paymentServiceKind($payment) : 'Layanan',
                'name' => $payment ? $this->reports->paymentServiceName($payment) : 'Layanan Platinum Gym',
            ],
            'labels' => [
                'invoiceStatus' => $this->invoiceStatusLabel($invoice->status),
                'paymentStatus' => $this->reports->statusLabel($payment?->status),
                'method' => filled($payment?->method) ? str((string) $payment->method)->headline()->toString() : '-',
            ],
            'amounts' => [
                'subtotal' => $this->reports->money($invoice->subtotal),
                'discount' => $this->reports->money($invoice->discount),
                'tax' => $this->reports->money($invoice->tax),
                'total' => $this->reports->money($invoice->total),
            ],
        ];
    }

    /** @return array<string, string> */
    private function settings(): array
    {
        $settings = Setting::query()
            ->whereIn('key', ['site_name', 'address', 'phone_display', 'public_email', 'invoice_footer'])
            ->pluck('value', 'key');

        return [
            'site_name' => $settings->get('site_name', 'Platinum Gym Padang'),
            'address' => $settings->get('address', 'Padang, Sumatera Barat'),
            'phone_display' => $settings->get('phone_display', '+62 821-7477-7761'),
            'public_email' => $settings->get('public_email', 'info@platinumgympadang.com'),
            'invoice_footer' => $settings->get('invoice_footer', 'Terima kasih telah bertransaksi di Platinum Gym Padang.'),
        ];
    }

    private function invoiceStatusLabel(?string $status): string
    {
        return match ((string) $status) {
            'paid' => 'Lunas',
            'issued' => 'Diterbitkan',
            'void' => 'Dibatalkan',
            default => filled($status) ? str($status)->headline()->toString() : '-',
        };
    }
}
