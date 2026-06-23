<?php

use Database\Seeders\RolePermissionSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Feature\Member\Support\MemberPortalFixtures as MemberFixtures;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

test('member profile pages use member layout and keep profile navigation active', function () {
    [$user] = MemberFixtures::member('PG-MEMBER-PROFILE-SHELL');

    $this->actingAs($user)->get(route('member.profile'))
        ->assertOk()
        ->assertSee('Profil Member')
        ->assertSee('Navigasi member')
        ->assertDontSee('Keamanan Akun Owner')
        ->assertDontSee('Keamanan Akun Admin');

    $this->actingAs($user)->get(route('member.profile.edit'))
        ->assertOk()
        ->assertSee('Edit Profil')
        ->assertSee('Nomor Induk Mahasiswa (NIM)')
        ->assertSee('accept="image/jpeg,image/png,image/webp"', false);
});

test('member profile update keeps validation errors near the submitted form', function () {
    [$user] = MemberFixtures::member('PG-MEMBER-PROFILE-VALIDATION');

    $this->actingAs($user)->from(route('member.profile.edit'))->patch(route('member.profile.update'), [
        'name' => '',
        'email' => 'bukan-email',
        'phone' => '123',
        'gender' => 'unknown',
        'birth_date' => '31/31/2020',
    ])
        ->assertRedirect(route('member.profile.edit'))
        ->assertSessionHasErrors(['name', 'email', 'phone', 'gender', 'birth_date']);
});

test('member profile avatar upload stores only member avatar path', function () {
    Storage::fake('public');

    [$user] = MemberFixtures::member('PG-MEMBER-PROFILE-AVATAR');

    $this->actingAs($user)->patch(route('member.profile.update'), [
        'name' => $user->name,
        'email' => $user->email,
        'phone' => $user->phone,
        'gender' => 'male',
        'birth_date' => '2000-01-01',
        'avatar' => UploadedFile::fake()->image('avatar.png', 256, 256)->size(256),
    ])->assertRedirect(route('member.profile'));

    expect($user->refresh()->avatar)->toStartWith('storage/member/avatars/');
    Storage::disk('public')->assertExists(str_replace('storage/', '', $user->avatar));
});
