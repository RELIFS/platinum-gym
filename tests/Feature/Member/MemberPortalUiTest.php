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
        ->assertSee('member-sidebar-nav-link', false)
        ->assertSee('member-sidebar-icon-frame', false)
        ->assertSee('member-sidebar-icon-svg', false)
        ->assertSee('data-member-sidebar-icon="membership-card"', false)
        ->assertSee('data-member-sidebar-icon="qr-scan"', false)
        ->assertSee('data-member-sidebar-icon="calendar-check"', false)
        ->assertSee('data-member-sidebar-icon="globe"', false)
        ->assertSee('data-member-website-link="desktop"', false)
        ->assertSee('data-member-website-link="mobile"', false)
        ->assertSee('data-member-website-placement="menu"', false)
        ->assertSee('Website Utama')
        ->assertSee(route('public.home'), false)
        ->assertSee('Buka Gymmi member', false)
        ->assertSee('data-chatbot-trigger', false)
        ->assertSee('gymmi-trigger', false)
        ->assertSee('gymmi-chat-trigger-160.webp', false)
        ->assertSee('gymmi-chat-trigger-320.webp', false)
        ->assertSee('gymmi-chat-trigger-480.webp', false)
        ->assertSee('srcset=', false)
        ->assertSee('sizes="(min-width: 768px) 156px, (min-width: 480px) 136px, 112px"', false)
        ->assertSee('data-gymmi-trigger-image', false)
        ->assertSee('gymmi-trigger-fallback', false)
        ->assertDontSee('gymmi-chat-trigger-96.webp', false)
        ->assertDontSee('gymmi-chat-trigger-256.webp', false)
        ->assertSee('aria-label="Pertanyaan cepat Gymmi"', false)
        ->assertSee('data-gymmi-panel-avatar', false)
        ->assertSee('data-gymmi-panel-avatar-image', false)
        ->assertSee('gymmi-panel-avatar-fallback', false)
        ->assertSee('avatar-gymmi-light-96.webp', false)
        ->assertSee('avatar-gymmi-light-192.webp', false)
        ->assertSee('avatar-gymmi-dark-96.webp', false)
        ->assertSee('avatar-gymmi-dark-192.webp', false)
        ->assertDontSee('avatar-gymmi-light.png', false)
        ->assertDontSee('avatar-gymmi-dark.png', false)
        ->assertSee('gymmi-quick-reply-rail', false)
        ->assertSee('x-on:click="closeMemberMenu()"', false)
        ->assertDontSee('Dashboard Admin')
        ->assertDontSee('Owner Portal')
        ->assertDontSee('data-member-sidebar-icon="circle"', false);

    $content = $response->getContent();

    expect(substr_count($content, 'Website Utama'))->toBe(2);
    expect(substr_count($content, 'member-sidebar-nav-link'))->toBeGreaterThanOrEqual(18);
    expect(substr_count($content, 'data-member-sidebar-icon="globe"'))->toBe(2);
    expect(substr_count($content, 'data-member-website-placement="menu"'))->toBe(2);
    expect(preg_match('/<a\b[^>]*data-member-website-link="desktop"[^>]*>/', $content, $desktopWebsiteLink))->toBe(1);
    expect(preg_match('/<a\b[^>]*data-member-website-link="mobile"[^>]*>/', $content, $mobileWebsiteLink))->toBe(1);
    expect($desktopWebsiteLink[0])->not->toContain('aria-current');
    expect($mobileWebsiteLink[0])->not->toContain('aria-current');
    expect($mobileWebsiteLink[0])->toContain('x-on:click="closeMemberMenu()"');
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
