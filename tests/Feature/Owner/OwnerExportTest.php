<?php

use Database\Seeders\RolePermissionSeeder;
use Tests\Feature\Owner\Support\OwnerPortalFixtures as OwnerFixtures;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

test('owner report csv export uses active filters and hides pending and sensitive data', function () {
    $owner = OwnerFixtures::owner();
    [, $member] = OwnerFixtures::member('PG-OWN-EXPORT');
    $paidPayment = OwnerFixtures::sensitivePayment($member);
    OwnerFixtures::payment($member, $paidPayment->payable, [
        'payment_code' => 'PAY-OWN-EXPORT-PENDING',
        'status' => 'waiting_confirmation',
        'paid_at' => null,
        'amount' => 999000,
    ]);

    $response = $this->actingAs($owner)->get(route('owner.reports.export', [
        'report_type' => 'finance',
        'q' => $paidPayment->payment_code,
    ]));

    $response->assertOk()
        ->assertHeader('content-type', 'text/csv; charset=UTF-8');

    expect((string) $response->headers->get('content-disposition'))
        ->toContain('laporan-owner-platinum-gym-finance-')
        ->toContain('.csv');

    expect($response->streamedContent())
        ->toContain($paidPayment->payment_code)
        ->not->toContain('PAY-OWN-EXPORT-PENDING')
        ->not->toContain(OwnerFixtures::SENSITIVE_SNAP_TOKEN)
        ->not->toContain(OwnerFixtures::SENSITIVE_REDIRECT_URL)
        ->not->toContain(OwnerFixtures::SENSITIVE_RAW_VALUE)
        ->not->toContain(OwnerFixtures::SENSITIVE_QR_TOKEN)
        ->not->toContain(OwnerFixtures::SENSITIVE_INTERNAL_NOTE);
});

test('owner report export supports xlsx pdf and unknown format fallback', function (string $format, string $expectedExtension) {
    $owner = OwnerFixtures::owner();
    [, $member] = OwnerFixtures::member();
    OwnerFixtures::payment($member);

    $response = $this->actingAs($owner)->get(route('owner.reports.export', array_filter([
        'report_type' => 'finance',
        'format' => $format,
    ])));

    $response->assertOk();
    expect((string) $response->headers->get('content-disposition'))->toContain($expectedExtension);
})->with([
    'xlsx' => ['xlsx', '.xlsx'],
    'pdf' => ['pdf', '.pdf'],
    'unknown fallback' => ['zip', '.csv'],
]);
