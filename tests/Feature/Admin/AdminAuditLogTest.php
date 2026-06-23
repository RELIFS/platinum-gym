<?php

use Database\Seeders\RolePermissionSeeder;
use Tests\Feature\Admin\Support\AdminPortalFixtures as AdminFixture;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

test('admin audit log renders user friendly activity without raw sensitive properties', function () {
    $admin = AdminFixture::admin(['name' => 'Admin Audit QA']);

    activity()
        ->causedBy($admin)
        ->event('updated')
        ->withProperties(['secret' => 'raw-audit-secret', 'ip' => '127.0.0.1'])
        ->log('Pengaturan website diperbarui dari admin.');

    $this->actingAs($admin)
        ->get('/admin/audit-log?event=updated')
        ->assertOk()
        ->assertSee('Audit Log')
        ->assertSee('updated')
        ->assertSee('Admin Audit QA')
        ->assertSee('Pengaturan website diperbarui dari admin.')
        ->assertDontSee('raw-audit-secret');
});
