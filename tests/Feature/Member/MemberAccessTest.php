<?php

use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Tests\Feature\Member\Support\MemberPortalFixtures as MemberFixtures;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

test('member portal routes require authentication', function (string $path) {
    $this->get($path)->assertRedirect('/login');
})->with([
    '/member/dashboard',
    '/member/profil',
    '/member/profil/edit',
    '/member/membership',
    '/member/booking-kelas',
    '/member/riwayat-booking',
    '/member/transaksi',
    '/member/qr',
    '/member/qr/download',
    '/member/notifikasi',
]);

test('member portal routes require complete member profile', function () {
    $user = User::factory()->create();
    $user->assignRole('member');

    $this->actingAs($user)->get('/member/membership')
        ->assertRedirect('/member/complete-profile');
});

test('admin and owner cannot access member portal', function (string $role) {
    $user = User::factory()->create();
    $user->assignRole($role);

    $this->actingAs($user)->get('/member/dashboard')->assertForbidden();
})->with(['admin', 'owner']);

test('complete member can access all member portal pages', function (string $path, string $text) {
    [$user] = MemberFixtures::portalMember('PG-MEMBER-ACCESS');

    $this->actingAs($user)->get($path)
        ->assertOk()
        ->assertSee($text)
        ->assertSee('Website Utama')
        ->assertSee(route('public.home'), false)
        ->assertDontSee('Member Area')
        ->assertDontSee('Akun Login')
        ->assertDontSee('Midtrans Sandbox');
})->with([
    ['/member/dashboard', 'Dashboard Member'],
    ['/member/profil', 'Profil Member'],
    ['/member/profil/edit', 'Edit Profil'],
    ['/member/membership', 'Membership'],
    ['/member/booking-kelas', 'Booking Kelas'],
    ['/member/riwayat-booking', 'Riwayat Booking'],
    ['/member/transaksi', 'Transaksi'],
    ['/member/qr', 'QR Member'],
    ['/member/notifikasi', 'Notifikasi'],
]);
