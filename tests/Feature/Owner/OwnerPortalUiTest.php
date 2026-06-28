<?php

use Database\Seeders\RolePermissionSeeder;
use Tests\Feature\Owner\Support\OwnerPortalFixtures as OwnerFixtures;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

test('owner layout renders navigation drawer topbar flash and accessible controls', function () {
    $owner = OwnerFixtures::owner();

    $response = $this->actingAs($owner)->get(route('owner.reports.finance'))->assertOk();
    $content = $response->getContent();

    $response
        ->assertSee('href="#owner-main"', false)
        ->assertSee('id="owner-main"', false)
        ->assertSee('id="owner-mobile-navigation"', false)
        ->assertSee('role="dialog"', false)
        ->assertSee('aria-modal="true"', false)
        ->assertSee('aria-controls="owner-mobile-navigation"', false)
        ->assertSee('aria-label="Buka navigasi owner"', false)
        ->assertSee('aria-label="Tutup navigasi owner"', false)
        ->assertSee('aria-label="Identitas owner"', false)
        ->assertSee('aria-label="Identitas owner mobile"', false)
        ->assertSee('data-owner-sidebar-logout="desktop"', false)
        ->assertSee('data-owner-sidebar-logout="mobile"', false);

    expect(substr_count($content, 'data-owner-sidebar-logout='))->toBe(2);
});

test('owner active navigation is single per desktop and mobile instance', function (string $routeName, string $activeRoute) {
    $owner = OwnerFixtures::owner();

    $response = $this->actingAs($owner)->get(route($routeName))->assertOk();
    $content = $response->getContent();

    expect(substr_count($content, 'data-owner-nav-active="true"'))->toBe(2);
    expect(preg_match_all('/data-owner-nav-route="'.preg_quote($activeRoute, '/').'"[^>]*data-owner-nav-active="true"/', $content))->toBe(2);
})->with([
    ['owner.dashboard', 'owner.dashboard'],
    ['owner.reports.index', 'owner.reports.index'],
    ['owner.reports.finance', 'owner.reports.finance'],
    ['owner.reports.members', 'owner.reports.members'],
    ['owner.reports.classes', 'owner.reports.classes'],
]);

test('owner report ui includes labels captions mobile card fallback and tablet scroll hint', function () {
    $owner = OwnerFixtures::owner();
    [, $member] = OwnerFixtures::member('PG-OWN-UI');
    $payment = OwnerFixtures::payment($member, null, ['payment_code' => 'PAY-OWN-UI']);
    OwnerFixtures::invoice($payment);

    $this->actingAs($owner)->get(route('owner.reports.finance', ['q' => 'PAY-OWN-UI']))
        ->assertOk()
        ->assertSee('for="report_type"', false)
        ->assertSee('for="date_from"', false)
        ->assertSee('for="date_to"', false)
        ->assertSee('for="method"', false)
        ->assertSee('for="q"', false)
        ->assertSee('caption class="sr-only"', false)
        ->assertSee('Geser tabel untuk melihat kolom lainnya.')
        ->assertSee('Lihat invoice')
        ->assertSee('owner-button-primary', false)
        ->assertSee('owner-button-secondary', false)
        ->assertSee('Cash');
});
