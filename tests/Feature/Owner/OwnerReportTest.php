<?php

use Database\Seeders\RolePermissionSeeder;
use Illuminate\Support\Carbon;
use Tests\Feature\Owner\Support\OwnerPortalFixtures as OwnerFixtures;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

test('owner report pages render shared report shell and empty state', function (string $routeName, string $title) {
    $owner = OwnerFixtures::owner();

    $this->actingAs($owner)->get(route($routeName))
        ->assertOk()
        ->assertSee('owner-main', false)
        ->assertSee($title)
        ->assertSee('Ringkasan laporan owner')
        ->assertSee('Belum ada data laporan pada filter ini.')
        ->assertSee('Unduh CSV')
        ->assertSee('Unduh Excel')
        ->assertSee('Unduh PDF');
})->with([
    ['owner.reports.index', 'Laporan Owner'],
    ['owner.reports.finance', 'Laporan Keuangan'],
    ['owner.reports.members', 'Laporan Member & Membership'],
    ['owner.reports.classes', 'Laporan Booking & Kelas'],
]);

test('owner report validation rejects invalid date range', function () {
    $owner = OwnerFixtures::owner();

    $this->actingAs($owner)->from(route('owner.reports.finance'))->get(route('owner.reports.finance', [
        'date_from' => now()->toDateString(),
        'date_to' => now()->subDay()->toDateString(),
    ]))->assertRedirect(route('owner.reports.finance'))
        ->assertSessionHasErrors('date_to');
});

test('owner report accepts long date range by capping it internally', function () {
    $owner = OwnerFixtures::owner();

    $this->actingAs($owner)->get(route('owner.reports.finance', [
        'date_from' => '2026-01-01',
        'date_to' => '2027-12-31',
    ]))->assertOk()
        ->assertSee('Laporan Keuangan');
});

test('finance report pagination preserves active query parameters', function () {
    Carbon::setTestNow(Carbon::parse('2026-06-30 12:00:00'));

    try {
        $owner = OwnerFixtures::owner();
        [, $member] = OwnerFixtures::member('PG-OWN-PAGE');
        $membership = OwnerFixtures::membership($member);

        foreach (range(1, 13) as $index) {
            OwnerFixtures::payment($member, $membership, [
                'payment_code' => 'PAY-OWN-PAGE-'.str_pad((string) $index, 2, '0', STR_PAD_LEFT),
                'method' => 'cash',
                'paid_at' => now()->subMinutes($index),
            ]);
        }

        $this->actingAs($owner)->get(route('owner.reports.finance', [
            'q' => 'PAY-OWN-PAGE',
            'method' => 'cash',
        ]))->assertOk()
            ->assertSee('PAY-OWN-PAGE-01')
            ->assertSee('page=2', false)
            ->assertSee('q=PAY-OWN-PAGE', false)
            ->assertSee('method=cash', false);
    } finally {
        Carbon::setTestNow();
    }
});
