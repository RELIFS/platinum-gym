<?php

use Database\Seeders\RolePermissionSeeder;
use Tests\Feature\Owner\Support\OwnerPortalFixtures as OwnerFixtures;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

test('owner can view invoice and receipt read only without sensitive payment fields', function (string $routeName) {
    $owner = OwnerFixtures::owner();
    [, $member] = OwnerFixtures::member('PG-OWN-INVOICE');
    $payment = OwnerFixtures::sensitivePayment($member);
    $invoice = OwnerFixtures::invoice($payment, ['invoice_number' => 'INV-OWN-SAFE']);

    $this->actingAs($owner)->get(route($routeName, $invoice))
        ->assertOk()
        ->assertSee('INV-OWN-SAFE')
        ->assertSee('Owner')
        ->assertSee('Rp 250.000')
        ->assertDontSee(OwnerFixtures::SENSITIVE_SNAP_TOKEN)
        ->assertDontSee(OwnerFixtures::SENSITIVE_REDIRECT_URL)
        ->assertDontSee(OwnerFixtures::SENSITIVE_RAW_VALUE)
        ->assertDontSee(OwnerFixtures::SENSITIVE_QR_TOKEN)
        ->assertDontSee(OwnerFixtures::SENSITIVE_INTERNAL_NOTE)
        ->assertDontSee('method="POST" action="'.route('owner.invoices.show', $invoice).'"', false)
        ->assertDontSee('method="POST" action="'.route('owner.invoices.receipt', $invoice).'"', false);
})->with([
    'owner.invoices.show',
    'owner.invoices.receipt',
]);

test('owner invoice pages keep finance navigation context active', function (string $routeName) {
    $owner = OwnerFixtures::owner();
    [, $member] = OwnerFixtures::member();
    $invoice = OwnerFixtures::invoice(OwnerFixtures::payment($member));

    $response = $this->actingAs($owner)->get(route($routeName, $invoice))->assertOk();
    $content = $response->getContent();

    expect(substr_count($content, 'data-owner-nav-active="true"'))->toBe(2);
    expect(preg_match_all('/data-owner-nav-route="owner\.reports\.finance"[^>]*data-owner-nav-active="true"/', $content))->toBe(2);
})->with([
    'owner.invoices.show',
    'owner.invoices.receipt',
]);

test('owner can download invoice and receipt pdf with export permission', function (?string $type) {
    $owner = OwnerFixtures::owner();
    [, $member] = OwnerFixtures::member();
    $invoice = OwnerFixtures::invoice(OwnerFixtures::payment($member));

    $response = $this->actingAs($owner)->get(route('owner.invoices.download', array_filter([
        'invoice' => $invoice,
        'type' => $type,
    ])));

    $response->assertOk();
    expect((string) $response->headers->get('content-disposition'))->toContain('.pdf');
})->with([
    'invoice' => [null],
    'receipt' => ['receipt'],
]);
