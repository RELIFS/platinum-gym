<?php

use Database\Seeders\RolePermissionSeeder;
use Tests\Feature\Admin\Support\AdminPortalFixtures as AdminFixture;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

test('admin dashboard renders production shell and operational widgets', function () {
    $admin = AdminFixture::admin(['name' => 'Admin Dashboard QA']);
    [, $member] = AdminFixture::member('PG-ADM-DASH');
    $package = AdminFixture::package(['name' => 'Membership Dashboard QA']);
    $membership = AdminFixture::membership($member, $package);
    AdminFixture::payment($member, $membership, [
        'payment_code' => 'PAY-ADM-DASH',
        'status' => 'paid',
        'method' => 'cash',
        'paid_at' => now(),
    ]);

    $this->actingAs($admin)
        ->get('/admin')
        ->assertOk()
        ->assertSee('Dashboard Admin')
        ->assertSee('Ringkasan admin')
        ->assertSee('Tren aktivitas')
        ->assertSee('Aktivitas terbaru')
        ->assertSee('admin-main', false)
        ->assertSee('public-skip-link', false)
        ->assertSee('admin-mobile-navigation', false)
        ->assertSee('role="dialog"', false)
        ->assertSee('aria-controls="admin-mobile-navigation"', false)
        ->assertSee('Admin Dashboard QA')
        ->assertSee('admin-status-success', false)
        ->assertDontSee('member-main', false)
        ->assertDontSee('owner-main', false);
});

test('admin data pages render table and mobile card fallbacks', function (string $path, string $needle) {
    $admin = AdminFixture::admin();
    [, $member] = AdminFixture::member('PG-ADM-TABLE');
    $package = AdminFixture::package(['name' => 'Paket Table Admin QA']);
    $membership = AdminFixture::membership($member, $package);
    AdminFixture::payment($member, $membership, ['payment_code' => 'PAY-ADM-TABLE']);
    AdminFixture::schedule();
    AdminFixture::product(['name' => 'Produk UI Admin QA']);

    $response = $this->actingAs($admin)
        ->get($path)
        ->assertOk()
        ->assertSee($needle)
        ->assertSee('admin-table-wrap', false)
        ->assertSee('admin-table-mobile-card', false)
        ->assertSee('admin-status-', false);

    if ($path !== '/admin/laporan') {
        $response->assertSee('data-admin-table-search', false);
    }
})->with([
    ['/admin/anggota', 'Anggota'],
    ['/admin/paket', 'Paket'],
    ['/admin/kelas', 'Kelas'],
    ['/admin/pembayaran', 'Pembayaran'],
    ['/admin/produk', 'Produk UI Admin QA'],
    ['/admin/laporan', 'Laporan'],
]);

test('admin flash banners expose readable status state', function () {
    $admin = AdminFixture::admin();

    $this->actingAs($admin)
        ->withSession(['status' => 'Operasi admin berhasil diproses.'])
        ->get('/admin')
        ->assertOk()
        ->assertSee('Operasi admin berhasil diproses.')
        ->assertSee('role="status"', false);

    $this->actingAs($admin)
        ->withSession(['status' => 'Operasi admin perlu diperiksa.', 'status_kind' => 'error'])
        ->get('/admin')
        ->assertOk()
        ->assertSee('Operasi admin perlu diperiksa.')
        ->assertSee('role="alert"', false);
});
