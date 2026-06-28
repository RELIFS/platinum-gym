<?php

use Database\Seeders\RolePermissionSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Feature\Owner\Support\OwnerPortalFixtures as OwnerFixtures;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

test('profile route renders owner shell profile photo and security forms for owner', function () {
    $owner = OwnerFixtures::owner();

    $this->actingAs($owner)->get(route('profile.edit'))
        ->assertOk()
        ->assertSee('owner-main', false)
        ->assertSee('Keamanan Akun Owner')
        ->assertSee('Foto Profil Owner')
        ->assertSee('name="avatar"', false)
        ->assertSee(route('owner.profile-photo.update'), false)
        ->assertSee('Perbarui nama dan alamat email akun owner')
        ->assertSee('Ubah Kata Sandi')
        ->assertSee(asset('images/owner/owner-avatar-default.webp'), false);
});

test('owner profile photo upload stores valid file and replaces only local owner avatar', function () {
    Storage::fake('public');

    $owner = OwnerFixtures::owner();
    Storage::disk('public')->put('owner/avatars/old-avatar.jpg', 'old-avatar');
    $owner->forceFill(['avatar' => 'storage/owner/avatars/old-avatar.jpg'])->save();

    $this->actingAs($owner)->from(route('profile.edit'))->patch(route('owner.profile-photo.update'), [
        'avatar' => UploadedFile::fake()->image('owner-avatar.jpg', 256, 256)->size(256),
    ])->assertRedirect(route('profile.edit'))
        ->assertSessionHas('status', 'owner-photo-updated');

    Storage::disk('public')->assertMissing('owner/avatars/old-avatar.jpg');

    $avatar = $owner->refresh()->avatar;
    expect($avatar)->toStartWith('storage/owner/avatars/');
    Storage::disk('public')->assertExists(str_replace('storage/', '', $avatar));
});

test('owner profile photo upload does not delete external avatar and rejects invalid files', function () {
    Storage::fake('public');

    $owner = OwnerFixtures::owner(['avatar' => 'https://example.test/owner-avatar.png']);

    $this->actingAs($owner)->from(route('profile.edit'))->patch(route('owner.profile-photo.update'), [
        'avatar' => UploadedFile::fake()->create('owner-avatar.svg', 12, 'image/svg+xml'),
    ])->assertRedirect(route('profile.edit'))
        ->assertSessionHasErrors('avatar');

    $this->actingAs($owner)->from(route('profile.edit'))->patch(route('owner.profile-photo.update'), [
        'avatar' => UploadedFile::fake()->image('owner-avatar.png', 256, 256)->size(3072),
    ])->assertRedirect(route('profile.edit'))
        ->assertSessionHasErrors('avatar');

    expect($owner->refresh()->avatar)->toBe('https://example.test/owner-avatar.png');
    expect(Storage::disk('public')->allFiles('owner/avatars'))->toBe([]);
});

test('member and admin cannot upload owner profile photo', function (string $role) {
    $user = OwnerFixtures::roleUser($role);

    $this->actingAs($user)->patch(route('owner.profile-photo.update'), [
        'avatar' => UploadedFile::fake()->image('owner-avatar.jpg', 256, 256)->size(256),
    ])->assertForbidden();
})->with(['member', 'admin']);
