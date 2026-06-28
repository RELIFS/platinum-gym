<?php

use Database\Seeders\RolePermissionSeeder;
use Tests\Feature\Owner\Support\OwnerPortalFixtures as OwnerFixtures;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

test('finance report only shows paid payments matching date method and search filters', function () {
    $owner = OwnerFixtures::owner();
    [, $member] = OwnerFixtures::member('PG-OWN-FINANCE');
    $membership = OwnerFixtures::membership($member);

    OwnerFixtures::payment($member, $membership, [
        'payment_code' => 'PAY-OWN-FINANCE-PAID',
        'method' => 'cash',
        'amount' => 200000,
        'paid_at' => now()->subDay(),
    ]);
    OwnerFixtures::payment($member, $membership, [
        'payment_code' => 'PAY-OWN-FINANCE-MIDTRANS',
        'method' => 'midtrans',
        'amount' => 300000,
        'paid_at' => now()->subDay(),
    ]);
    OwnerFixtures::payment($member, $membership, [
        'payment_code' => 'PAY-OWN-FINANCE-PENDING',
        'method' => 'cash',
        'amount' => 900000,
        'status' => 'waiting_confirmation',
        'paid_at' => null,
    ]);

    $this->actingAs($owner)->get(route('owner.reports.finance', [
        'date_from' => now()->subWeek()->toDateString(),
        'date_to' => now()->toDateString(),
        'method' => 'cash',
        'q' => 'PAY-OWN-FINANCE',
    ]))->assertOk()
        ->assertSee('PAY-OWN-FINANCE-PAID')
        ->assertSee('Rp 200.000')
        ->assertDontSee('PAY-OWN-FINANCE-MIDTRANS')
        ->assertDontSee('PAY-OWN-FINANCE-PENDING')
        ->assertDontSee('Rp 1.100.000');
});

test('finance report hides sensitive payment and qr values from html', function () {
    $owner = OwnerFixtures::owner();
    [, $member] = OwnerFixtures::member('PG-OWN-FIN-SAFE');
    $payment = OwnerFixtures::sensitivePayment($member);
    OwnerFixtures::invoice($payment);

    $this->actingAs($owner)->get(route('owner.reports.finance', [
        'q' => $payment->payment_code,
    ]))->assertOk()
        ->assertSee($payment->payment_code)
        ->assertDontSee(OwnerFixtures::SENSITIVE_SNAP_TOKEN)
        ->assertDontSee(OwnerFixtures::SENSITIVE_REDIRECT_URL)
        ->assertDontSee(OwnerFixtures::SENSITIVE_RAW_VALUE)
        ->assertDontSee(OwnerFixtures::SENSITIVE_QR_TOKEN)
        ->assertDontSee(OwnerFixtures::SENSITIVE_INTERNAL_NOTE);
});
