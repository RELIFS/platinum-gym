<?php

use Database\Seeders\RolePermissionSeeder;
use Tests\Feature\Admin\Support\AdminPortalFixtures as AdminFixture;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

test('admin portal get routes require authentication', function (string $path) {
    $this->get($path)->assertRedirect('/login');
})->with([
    '/admin',
    '/admin/check-in',
    '/admin/booking',
    '/admin/notifikasi',
    '/admin/anggota',
    '/admin/paket',
    '/admin/kelas',
    '/admin/pembayaran',
    '/admin/produk',
    '/admin/galeri',
    '/admin/testimoni',
    '/admin/promo',
    '/admin/trainer',
    '/admin/laporan',
    '/admin/audit-log',
    '/admin/pengaturan',
    '/admin/profil',
    '/admin/resource/products/tambah',
    '/admin/resource/products/1/edit',
    '/admin/invoice/1',
    '/admin/invoice/1/struk',
]);

test('non admin roles cannot access admin portal pages', function (string $role) {
    $this->actingAs(AdminFixture::roleUser($role))
        ->get('/admin/pembayaran')
        ->assertForbidden();
})->with(['member', 'owner']);

test('admin verified user can open primary admin pages', function (string $path, string $heading) {
    $admin = AdminFixture::admin();

    $this->actingAs($admin)
        ->get($path)
        ->assertOk()
        ->assertSee($heading)
        ->assertSee('admin-main', false)
        ->assertSee('admin-mobile-navigation', false)
        ->assertDontSee('member-main', false)
        ->assertDontSee('owner-main', false);
})->with([
    ['/admin', 'Dashboard Admin'],
    ['/admin/check-in', 'Check-in'],
    ['/admin/booking', 'Booking Kelas'],
    ['/admin/notifikasi', 'Notifikasi'],
    ['/admin/anggota', 'Anggota'],
    ['/admin/paket', 'Paket'],
    ['/admin/kelas', 'Kelas'],
    ['/admin/pembayaran', 'Pembayaran'],
    ['/admin/produk', 'Produk'],
    ['/admin/galeri', 'Galeri'],
    ['/admin/testimoni', 'Testimoni'],
    ['/admin/promo', 'Promo'],
    ['/admin/trainer', 'Trainer'],
    ['/admin/laporan', 'Laporan'],
    ['/admin/audit-log', 'Audit Log'],
    ['/admin/pengaturan', 'Pengaturan'],
    ['/admin/profil', 'Profil Admin'],
]);

test('action routes enforce their specific admin permissions', function (string $permission, string $method, string $routeName, array $routeParams, array $payload = []) {
    $admin = AdminFixture::admin();
    [, $member] = AdminFixture::member();
    $package = AdminFixture::package();
    $membership = AdminFixture::membership($member, $package, ['status' => 'pending_payment']);
    $payment = AdminFixture::payment($member, $membership);
    $invoice = AdminFixture::invoice($payment);
    $enrollment = AdminFixture::enrollment($member);

    $routeParams = array_map(fn (mixed $value): mixed => match ($value) {
        ':payment' => $payment,
        ':invoice' => $invoice,
        ':enrollment' => $enrollment,
        default => $value,
    }, $routeParams);

    AdminFixture::revokeAdminPermission($permission);

    $this->actingAs($admin)
        ->{$method}(route($routeName, $routeParams), $payload)
        ->assertForbidden();
})->with([
    ['input_cash_payments', 'post', 'admin.payments.cash', [], ['member_id' => 1, 'package_id' => 1]],
    ['verify_payments', 'post', 'admin.payments.approve', [':payment']],
    ['verify_payments', 'post', 'admin.payments.reject', [':payment'], ['reason' => 'Bukti pembayaran belum sesuai.']],
    ['manage_bookings', 'post', 'admin.booking.store', [], ['member_id' => 1, 'schedule_id' => 1, 'session_date' => now()->toDateString()]],
    ['manage_bookings', 'post', 'admin.booking.confirm', [':enrollment']],
    ['manage_bookings', 'post', 'admin.booking.cancel', [':enrollment']],
    ['scan_qr', 'post', 'admin.check-in.preview', [], ['token' => str_repeat('a', 64)]],
    ['scan_qr', 'post', 'admin.check-in.confirm', [], ['preview_key' => 'missing', 'action' => 'check_in_membership']],
    ['manage_products', 'get', 'admin.resources.create', ['products']],
    ['manage_settings', 'patch', 'admin.settings.update', []],
    ['export_operational_reports', 'get', 'admin.reports.export', []],
]);
