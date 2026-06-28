<?php

use Database\Seeders\RolePermissionSeeder;
use Tests\Feature\Owner\Support\OwnerPortalFixtures as OwnerFixtures;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

test('owner dashboard renders business monitoring shell and hides sensitive payment fields', function () {
    $owner = OwnerFixtures::owner();
    [, $member] = OwnerFixtures::member('PG-OWN-DASH');
    $paidPayment = OwnerFixtures::sensitivePayment($member);
    OwnerFixtures::payment($member, $paidPayment->payable, [
        'payment_code' => 'PAY-OWN-PENDING-DASH',
        'amount' => 900000,
        'status' => 'waiting_confirmation',
        'paid_at' => null,
    ]);
    OwnerFixtures::enrollment($member);

    $response = $this->actingAs($owner)->get(route('owner.dashboard'))
        ->assertOk()
        ->assertSee('owner-main', false)
        ->assertSee('Dashboard Owner')
        ->assertSee('Pendapatan periode ini')
        ->assertSee('Transaksi terkonfirmasi')
        ->assertSee('Member aktif')
        ->assertSee('Membership aktif')
        ->assertSee('Booking periode ini')
        ->assertSee('owner-business-trend-chart', false)
        ->assertSee($paidPayment->payment_code)
        ->assertDontSee('PAY-OWN-PENDING-DASH')
        ->assertDontSee('Rp 1.150.000')
        ->assertDontSee(OwnerFixtures::SENSITIVE_SNAP_TOKEN)
        ->assertDontSee(OwnerFixtures::SENSITIVE_REDIRECT_URL)
        ->assertDontSee(OwnerFixtures::SENSITIVE_RAW_VALUE)
        ->assertDontSee(OwnerFixtures::SENSITIVE_QR_TOKEN)
        ->assertDontSee(OwnerFixtures::SENSITIVE_INTERNAL_NOTE);

    expect($response->getContent())->toContain('Rp 250.000');
});

test('owner dashboard renders empty state safely without operational data', function () {
    $owner = OwnerFixtures::owner();

    $this->actingAs($owner)->get(route('owner.dashboard'))
        ->assertOk()
        ->assertSee('Dashboard Owner')
        ->assertSee('Belum ada pendapatan terkonfirmasi pada periode ini.')
        ->assertSee('Belum ada transaksi terkonfirmasi.')
        ->assertSee('Tidak ada membership aktif yang berakhir dalam 14 hari.');
});
