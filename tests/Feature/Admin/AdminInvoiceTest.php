<?php

use Database\Seeders\RolePermissionSeeder;
use Tests\Feature\Admin\Support\AdminPortalFixtures as AdminFixture;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

test('admin can view invoice and receipt in admin financial context without sensitive payment data', function (string $routeName, string $title) {
    $admin = AdminFixture::admin();
    [, $member] = AdminFixture::member('PG-ADM-INVOICE');
    $membership = AdminFixture::membership($member);
    $payment = AdminFixture::payment($member, $membership, [
        'payment_code' => 'PAY-ADM-INVOICE',
        'status' => 'paid',
        'method' => 'midtrans',
        'midtrans_snap_token' => 'invoice-secret-snap-token',
        'midtrans_redirect_url' => 'https://payment.example.test/invoice-secret',
        'midtrans_raw_response' => ['secret' => 'invoice-raw-secret'],
        'note' => 'Catatan internal invoice admin',
    ]);
    $invoice = AdminFixture::invoice($payment, [
        'invoice_number' => 'INV-ADM-SAFE',
        'status' => 'paid',
    ]);

    $response = $this->actingAs($admin)
        ->get(route($routeName, $invoice))
        ->assertOk()
        ->assertSee($title)
        ->assertSee('INV-ADM-SAFE')
        ->assertSee('PAY-ADM-INVOICE')
        ->assertDontSee('invoice-secret-snap-token')
        ->assertDontSee('invoice-secret')
        ->assertDontSee('invoice-raw-secret')
        ->assertDontSee('Catatan internal invoice admin');

    if ($routeName === 'admin.invoices.show') {
        $response->assertSee('admin-main', false);
    }
})->with([
    ['admin.invoices.show', 'Invoice Transaksi'],
    ['admin.invoices.receipt', 'Struk Transaksi'],
]);

test('admin can download invoice and receipt pdf documents', function (?string $type, string $extensionNeedle) {
    $admin = AdminFixture::admin();
    [, $member] = AdminFixture::member('PG-ADM-INVOICE-DOWNLOAD');
    $payment = AdminFixture::payment($member, AdminFixture::membership($member), [
        'status' => 'paid',
        'method' => 'cash',
    ]);
    $invoice = AdminFixture::invoice($payment, ['status' => 'paid']);

    $url = route('admin.invoices.download', array_filter([
        'invoice' => $invoice,
        'type' => $type,
    ]));

    $response = $this->actingAs($admin)->get($url);

    $response->assertOk();
    expect($response->headers->get('content-disposition'))->toContain($extensionNeedle);
})->with([
    [null, '.pdf'],
    ['receipt', '.pdf'],
]);
