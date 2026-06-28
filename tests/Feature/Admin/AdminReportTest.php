<?php

use Database\Seeders\RolePermissionSeeder;
use Tests\Feature\Admin\Support\AdminPortalFixtures as AdminFixture;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

test('admin report page keeps filters and export actions visible', function () {
    $admin = AdminFixture::admin();

    $this->actingAs($admin)
        ->get('/admin/laporan?date_from='.now()->subWeek()->toDateString().'&date_to='.now()->toDateString())
        ->assertOk()
        ->assertSee('Laporan')
        ->assertSee('Ringkasan Laporan')
        ->assertSee('Unduh CSV')
        ->assertSee('Unduh Excel')
        ->assertSee('Unduh PDF')
        ->assertSee('date_from', false)
        ->assertSee('date_to', false)
        ->assertSee('format=xlsx', false)
        ->assertSee('format=pdf', false);
});

test('admin operational reports export csv xlsx and pdf formats', function (string $format, string $extension) {
    $admin = AdminFixture::admin();

    $response = $this->actingAs($admin)
        ->get(route('admin.reports.export', ['format' => $format]));

    $response->assertOk();

    expect($response->headers->get('content-disposition'))->toContain($extension);
})->with([
    ['csv', '.csv'],
    ['xlsx', '.xlsx'],
    ['pdf', '.pdf'],
]);

test('admin report export falls back to csv when format is empty', function () {
    $admin = AdminFixture::admin();

    $response = $this->actingAs($admin)->get(route('admin.reports.export'));

    $response->assertOk();
    expect($response->headers->get('content-disposition'))->toContain('.csv');
});
