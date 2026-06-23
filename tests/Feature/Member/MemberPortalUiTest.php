<?php

use Database\Seeders\RolePermissionSeeder;
use Tests\Feature\Member\Support\MemberPortalFixtures as MemberFixtures;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

test('complete member pages render consistent member layout smoke', function (string $routeName, string $heading) {
    [$user] = MemberFixtures::member('PG-MEMBER-UI-'.str_replace('.', '-', $routeName));

    $response = $this->actingAs($user)->get(route($routeName));

    $response
        ->assertOk()
        ->assertSee($heading)
        ->assertSee('Navigasi member')
        ->assertSee('member-main', false)
        ->assertSee('member-mobile-menu-button', false)
        ->assertSee('data-member-website-link="desktop"', false)
        ->assertSee('data-member-website-link="mobile"', false)
        ->assertSee('data-member-website-placement="menu"', false)
        ->assertSee('Website Utama')
        ->assertSee(route('public.home'), false)
        ->assertSee('Buka Gymmi member', false)
        ->assertDontSee('Dashboard Admin')
        ->assertDontSee('Owner Portal');

    expect(substr_count($response->getContent(), 'Website Utama'))->toBe(2);
    expect(substr_count($response->getContent(), 'data-member-website-placement="menu"'))->toBe(2);
})->with([
    ['member.dashboard', 'Dashboard Member'],
    ['member.profile', 'Profil Member'],
    ['member.profile.edit', 'Edit Profil'],
    ['member.membership', 'Membership'],
    ['member.booking', 'Booking Kelas'],
    ['member.bookings', 'Riwayat Booking'],
    ['member.transactions', 'Transaksi'],
    ['member.qr', 'QR Member'],
    ['member.notifications', 'Notifikasi'],
]);

test('member flash banners expose accessible live regions for success and error states', function () {
    [$user] = MemberFixtures::member('PG-MEMBER-UI-FLASH');

    $this->actingAs($user)
        ->withSession(['status' => 'Berhasil disimpan.', 'status_kind' => 'success'])
        ->get(route('member.dashboard'))
        ->assertOk()
        ->assertSee('Berhasil disimpan.')
        ->assertSee('aria-live="polite"', false);

    $this->actingAs($user)
        ->withSession(['status' => 'Terjadi kesalahan.', 'status_kind' => 'error'])
        ->get(route('member.dashboard'))
        ->assertOk()
        ->assertSee('Terjadi kesalahan.')
        ->assertSee('role="alert"', false);
});

test('member complete profile route stays available only for member onboarding state', function () {
    $member = MemberFixtures::incompleteMember();
    $admin = MemberFixtures::roleUser('admin');

    $this->actingAs($member)->get(route('member.profile.complete'))
        ->assertOk()
        ->assertSee('Lengkapi')
        ->assertSee('Profil');

    $this->actingAs($admin)->get(route('member.profile.complete'))->assertForbidden();
});
