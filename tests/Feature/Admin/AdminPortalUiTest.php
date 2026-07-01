<?php

use Database\Seeders\RolePermissionSeeder;
use Tests\Feature\Admin\Support\AdminPortalFixtures as AdminFixture;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

test('admin layout exposes accessible navigation controls and identity regions', function () {
    $admin = AdminFixture::admin([
        'name' => 'Admin UI QA',
        'email' => 'admin.ui.qa@example.test',
    ]);

    $response = $this->actingAs($admin)->get('/admin/produk');
    $content = $response->getContent();

    $response
        ->assertOk()
        ->assertSee('Lewati navigasi admin')
        ->assertSee('aria-controls="admin-mobile-navigation"', false)
        ->assertSee('aria-label="Buka navigasi admin"', false)
        ->assertSee('role="dialog"', false)
        ->assertSee('aria-modal="true"', false)
        ->assertSee('Menu admin mobile')
        ->assertSee('Identitas admin')
        ->assertSee('Admin UI QA')
        ->assertSee('admin.ui.qa@example.test')
        ->assertSee('Keluar')
        ->assertSee('data-portal-account-menu="admin"', false)
        ->assertSee('data-portal-account-trigger="admin"', false)
        ->assertSee('data-portal-account-dropdown="admin"', false)
        ->assertSee('data-portal-account-profile="admin"', false)
        ->assertSee('data-portal-account-logout="admin"', false)
        ->assertSee(route('admin.profile'), false)
        ->assertSee('admin-sidebar-nav-link', false)
        ->assertSee('admin-sidebar-nav-link-active', false)
        ->assertSee('admin-sidebar-icon-frame', false)
        ->assertSee('admin-sidebar-icon-svg', false)
        ->assertSee('data-admin-sidebar-icon="qr-scan"', false)
        ->assertSee('data-admin-sidebar-icon="calendar-check"', false)
        ->assertSee('data-admin-sidebar-icon="membership-card"', false)
        ->assertSee('data-admin-sidebar-icon="dumbbell"', false)
        ->assertSee('data-admin-sidebar-icon="coach"', false)
        ->assertSee('data-admin-sidebar-icon="history"', false)
        ->assertSee('Website Utama')
        ->assertSee(route('public.home'), false)
        ->assertSee('data-admin-website-link="desktop"', false)
        ->assertSee('data-admin-website-link="mobile"', false)
        ->assertSee('data-admin-sidebar-icon="globe"', false)
        ->assertDontSee('admin-nav-link-active', false);

    expect(substr_count($content, 'Website Utama'))->toBe(2)
        ->and(substr_count($content, 'aria-label="Identitas admin"'))->toBe(1)
        ->and(substr_count($content, 'data-portal-account-menu="admin"'))->toBe(1)
        ->and(substr_count($content, 'data-portal-account-logout="admin"'))->toBe(1)
        ->and(substr_count($content, 'data-admin-sidebar-icon="globe"'))->toBe(2)
        ->and((bool) preg_match('/<a(?=[^>]*data-admin-website-link="mobile")(?=[^>]*x-on:click="closeAdminMenu\(\)")/s', $content))->toBeTrue()
        ->and((bool) preg_match('/<a(?=[^>]*data-admin-website-link="(?:desktop|mobile)")(?=[^>]*aria-current=)/s', $content))->toBeFalse();
});

test('admin forms expose labels helper text and validation state near fields', function () {
    $admin = AdminFixture::admin();

    $this->actingAs($admin)
        ->get(route('admin.resources.create', 'products'))
        ->assertOk()
        ->assertSee('Nama Produk')
        ->assertSee('placeholder="Contoh: Whey Protein"', false)
        ->assertSee('Harga')
        ->assertSee('Stok')
        ->assertSee('Foto Produk')
        ->assertSee('Deskripsi Foto');

    $this->actingAs($admin)
        ->from(route('admin.resources.create', 'products'))
        ->post(route('admin.resources.store', 'products'), [
            'name' => '',
            'price' => '',
            'stock' => '',
        ])
        ->assertRedirect(route('admin.resources.create', 'products'))
        ->assertSessionHasErrors(['name', 'price', 'stock']);
});

test('admin resource forms use context aware placeholders for shared field names', function () {
    $admin = AdminFixture::admin();

    $this->actingAs($admin)
        ->get(route('admin.resources.create', 'members'))
        ->assertOk()
        ->assertSee('Nama Lengkap')
        ->assertSee('placeholder="Contoh: Muhammad Luthfi"', false)
        ->assertDontSee('placeholder="Contoh: Gym Umum 1 Bulan"', false);

    $this->actingAs($admin)
        ->get(route('admin.resources.create', 'packages'))
        ->assertOk()
        ->assertSee('Nama Paket')
        ->assertSee('placeholder="Contoh: Gym Umum 1 Bulan"', false);

    $this->actingAs($admin)
        ->get(route('admin.resources.create', 'trainers'))
        ->assertOk()
        ->assertSee('Nama Trainer')
        ->assertSee('placeholder="Contoh: Coach Riko"', false);
});

test('admin status and action controls keep text labels instead of color only state', function () {
    $admin = AdminFixture::admin();
    AdminFixture::product(['name' => 'Produk Status Text QA', 'is_active' => true]);

    $this->actingAs($admin)
        ->get('/admin/produk')
        ->assertOk()
        ->assertSee('Produk Status Text QA')
        ->assertSee('Aktif')
        ->assertSee('admin-status-neutral', false)
        ->assertSee('admin-button-secondary', false)
        ->assertDontSee('style="background-color', false);
});
