<?php

use Database\Seeders\RolePermissionSeeder;
use Tests\Feature\Admin\Support\AdminPortalFixtures as AdminFixture;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

test('admin layout exposes accessible navigation controls and identity regions', function () {
    $admin = AdminFixture::admin(['name' => 'Admin UI QA']);

    $this->actingAs($admin)
        ->get('/admin/produk')
        ->assertOk()
        ->assertSee('Lewati navigasi admin')
        ->assertSee('aria-controls="admin-mobile-navigation"', false)
        ->assertSee('aria-label="Buka navigasi admin"', false)
        ->assertSee('role="dialog"', false)
        ->assertSee('aria-modal="true"', false)
        ->assertSee('Menu admin mobile')
        ->assertSee('Identitas admin')
        ->assertSee('Admin UI QA')
        ->assertSee('Keluar');
});

test('admin forms expose labels helper text and validation state near fields', function () {
    $admin = AdminFixture::admin();

    $this->actingAs($admin)
        ->get(route('admin.resources.create', 'products'))
        ->assertOk()
        ->assertSee('Nama Produk')
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
