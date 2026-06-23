<?php

use Database\Seeders\RolePermissionSeeder;
use Tests\Feature\PublicWebsite\Support\PublicWebsiteFixtures as PublicFixtures;

test('public get routes are accessible by guests without login redirects', function (string $path) {
    $this->get($path)
        ->assertOk()
        ->assertDontSee('name="email"', false)
        ->assertSee('main-content', false);
})->with(PublicFixtures::getRoutes());

test('authenticated users can still open public website pages', function (string $role) {
    $this->seed(RolePermissionSeeder::class);

    $user = PublicFixtures::user(['email' => $role.'.public.'.Str::random(6).'@example.test']);
    $user->assignRole($role);

    $this->actingAs($user)
        ->get(route('public.home'))
        ->assertOk()
        ->assertSee('Dashboard')
        ->assertDontSee('/member/dashboard"', false)
        ->assertDontSee('/admin"', false)
        ->assertDontSee('/owner"', false);
})->with(['member', 'admin', 'owner']);

test('public pages do not expose sensitive operational settings or internal identifiers', function (string $path) {
    PublicFixtures::publicSettings();
    PublicFixtures::sensitiveSettings();

    $response = $this->get($path)->assertOk();

    foreach (PublicFixtures::SENSITIVE_FRAGMENTS as $fragment) {
        $response->assertDontSee($fragment, false);
    }
})->with(PublicFixtures::getRoutes());
