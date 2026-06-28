<?php

use Database\Seeders\RolePermissionSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Feature\Admin\Support\AdminPortalFixtures as AdminFixture;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

test('admin profile page renders account summary avatar upload and account actions', function () {
    $admin = AdminFixture::admin([
        'name' => 'Admin Profile QA',
        'avatar' => 'storage/admin/avatars/current.jpg',
        'last_login_at' => now()->subHour(),
    ]);

    $this->actingAs($admin)
        ->get(route('admin.profile'))
        ->assertOk()
        ->assertSee('Profil Admin')
        ->assertSee('Admin Profile QA')
        ->assertSee(asset('storage/admin/avatars/current.jpg'), false)
        ->assertSee('Foto profil admin')
        ->assertSee('name="avatar"', false)
        ->assertSee('accept="image/jpeg,image/png,image/webp"', false)
        ->assertSee(route('admin.profile-photo.update'), false)
        ->assertSee('Kelola Keamanan Akun')
        ->assertSee('Buka Audit Log');
});

test('admin profile photo upload stores valid file and replaces only local admin avatar', function () {
    Storage::fake('public');

    $admin = AdminFixture::admin();
    Storage::disk('public')->put('admin/avatars/old-avatar.jpg', 'old-avatar');
    $admin->forceFill(['avatar' => 'storage/admin/avatars/old-avatar.jpg'])->save();

    $this->actingAs($admin)
        ->from(route('admin.profile'))
        ->patch(route('admin.profile-photo.update'), [
            'avatar' => UploadedFile::fake()->image('admin-avatar.jpg', 256, 256)->size(256),
        ])
        ->assertRedirect(route('admin.profile'))
        ->assertSessionHas('status', 'admin-photo-updated');

    Storage::disk('public')->assertMissing('admin/avatars/old-avatar.jpg');

    $avatar = $admin->refresh()->avatar;
    expect($avatar)->toStartWith('storage/admin/avatars/');
    Storage::disk('public')->assertExists(str_replace('storage/', '', $avatar));
});

test('admin profile photo upload does not delete external member or owner avatar paths', function (string $oldAvatarPath) {
    Storage::fake('public');

    $admin = AdminFixture::admin(['avatar' => $oldAvatarPath]);
    Storage::disk('public')->put(str_replace('storage/', '', $oldAvatarPath), 'old-avatar');

    $this->actingAs($admin)
        ->from(route('admin.profile'))
        ->patch(route('admin.profile-photo.update'), [
            'avatar' => UploadedFile::fake()->image('admin-avatar.webp', 256, 256)->size(256),
        ])
        ->assertRedirect(route('admin.profile'));

    Storage::disk('public')->assertExists(str_replace('storage/', '', $oldAvatarPath));
    expect($admin->refresh()->avatar)->toStartWith('storage/admin/avatars/');
})->with([
    'member avatar path' => 'storage/member/avatars/member-avatar.jpg',
    'owner avatar path' => 'storage/owner/avatars/owner-avatar.jpg',
]);

test('admin profile photo upload replaces external avatar without trying to delete it', function () {
    Storage::fake('public');

    $admin = AdminFixture::admin(['avatar' => 'https://example.test/admin-avatar.png']);

    $this->actingAs($admin)
        ->from(route('admin.profile'))
        ->patch(route('admin.profile-photo.update'), [
            'avatar' => UploadedFile::fake()->image('admin-avatar.png', 256, 256)->size(256),
        ])
        ->assertRedirect(route('admin.profile'));

    expect($admin->refresh()->avatar)->toStartWith('storage/admin/avatars/')
        ->and(Storage::disk('public')->allFiles('admin/avatars'))->toHaveCount(1);
});

test('admin profile photo upload rejects invalid files', function (UploadedFile $file) {
    Storage::fake('public');

    $admin = AdminFixture::admin();

    $this->actingAs($admin)
        ->from(route('admin.profile'))
        ->patch(route('admin.profile-photo.update'), [
            'avatar' => $file,
        ])
        ->assertRedirect(route('admin.profile'))
        ->assertSessionHasErrors('avatar');

    expect(Storage::disk('public')->allFiles('admin/avatars'))->toBe([]);
})->with([
    'svg' => fn () => UploadedFile::fake()->create('admin-avatar.svg', 12, 'image/svg+xml'),
    'text' => fn () => UploadedFile::fake()->create('admin-avatar.txt', 4, 'text/plain'),
    'oversized' => fn () => UploadedFile::fake()->image('admin-avatar.jpg', 256, 256)->size(3072),
]);

test('non admin users and guests cannot upload admin profile photo', function (?string $role) {
    $request = $role
        ? $this->actingAs(AdminFixture::roleUser($role))
        : $this;

    $response = $request->patch(route('admin.profile-photo.update'), [
        'avatar' => UploadedFile::fake()->image('admin-avatar.jpg', 256, 256)->size(256),
    ]);

    $role ? $response->assertForbidden() : $response->assertRedirect('/login');
})->with([
    'guest' => null,
    'member' => 'member',
    'owner' => 'owner',
]);

test('admin shell renders avatar image and keeps fallback identity available', function () {
    $admin = AdminFixture::admin([
        'name' => 'Admin Shell Avatar',
        'avatar' => 'storage/admin/avatars/shell-avatar.jpg',
    ]);

    $this->actingAs($admin)
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertSee('Admin Shell Avatar')
        ->assertSee(asset('storage/admin/avatars/shell-avatar.jpg'), false)
        ->assertSee('Identitas admin');

    $fallback = AdminFixture::admin(['name' => 'Fallback Admin', 'avatar' => null]);

    $this->actingAs($fallback)
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertSee('Fallback Admin')
        ->assertSee('Identitas admin');
});

test('admin account security page keeps admin shell identity and accessible password controls', function () {
    $admin = AdminFixture::admin([
        'name' => 'Admin Security QA',
        'avatar' => 'storage/admin/avatars/security-avatar.jpg',
    ]);

    $this->actingAs($admin)
        ->get(route('profile.edit'))
        ->assertOk()
        ->assertSee('Keamanan Akun Admin')
        ->assertSee('Admin Security QA')
        ->assertSee(asset('storage/admin/avatars/security-avatar.jpg'), false)
        ->assertSee('Kembali ke Profil Admin')
        ->assertSee('x-bind:aria-pressed="show1.toString()"', false)
        ->assertSee('Tampilkan kata sandi saat ini', false)
        ->assertSee('admin-card', false)
        ->assertDontSee('member-main', false)
        ->assertDontSee('owner-main', false);
});
