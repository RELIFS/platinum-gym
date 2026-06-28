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
        ->assertSee('Bukti Mahasiswa')
        ->assertSee('name="student_proof"', false)
        ->assertDontSee('Nomor Induk Mahasiswa (NIM)')
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

test('student member profile requires proof image before saving student status', function () {
    Storage::fake('local');

    [$user] = MemberFixtures::member('PG-MEMBER-PROFILE-STUDENT-PROOF-REQUIRED');

    $this->actingAs($user)->from(route('member.profile.edit'))->patch(route('member.profile.update'), [
        'name' => $user->name,
        'email' => $user->email,
        'phone' => $user->phone,
        'gender' => 'male',
        'birth_date' => '2000-01-01',
        'is_student' => '1',
    ])->assertRedirect(route('member.profile.edit'))
        ->assertSessionHasErrors('student_proof');

    expect(Storage::disk('local')->allFiles('member/student-proofs'))->toBe([]);
});

test('student proof upload is stored privately and served only through member route', function () {
    Storage::fake('local');

    [$user, $member] = MemberFixtures::member('PG-MEMBER-PROFILE-STUDENT-PROOF');

    $this->actingAs($user)->patch(route('member.profile.update'), [
        'name' => $user->name,
        'email' => $user->email,
        'phone' => $user->phone,
        'gender' => 'male',
        'birth_date' => '2000-01-01',
        'is_student' => '1',
        'student_proof' => UploadedFile::fake()->image('ktm.jpg', 480, 320)->size(256),
    ])->assertRedirect(route('member.profile'));

    $member->refresh();

    expect($member->student_proof_path)
        ->toStartWith('member/student-proofs/')
        ->and($member->student_proof_uploaded_at)->not->toBeNull()
        ->and($member->student_verification_status)->toBe('pending_review')
        ->and($member->student_verification_source)->toBe('member_upload');

    Storage::disk('local')->assertExists($member->student_proof_path);

    $this->actingAs($user)->get(route('member.profile.student-proof'))
        ->assertOk()
        ->assertHeader('X-Content-Type-Options', 'nosniff')
        ->assertHeader('Cache-Control', 'max-age=300, private');
});

test('student proof upload rejects invalid files without storing them', function () {
    Storage::fake('local');

    [$user] = MemberFixtures::member('PG-MEMBER-PROFILE-STUDENT-PROOF-INVALID');

    $this->actingAs($user)->from(route('member.profile.edit'))->patch(route('member.profile.update'), [
        'name' => $user->name,
        'email' => $user->email,
        'phone' => $user->phone,
        'gender' => 'male',
        'birth_date' => '2000-01-01',
        'is_student' => '1',
        'student_proof' => UploadedFile::fake()->create('portal.pdf', 128, 'application/pdf'),
    ])->assertRedirect(route('member.profile.edit'))
        ->assertSessionHasErrors('student_proof');

    expect(Storage::disk('local')->allFiles('member/student-proofs'))->toBe([]);
});

test('student proof upload replaces old private proof and unchecking student removes it', function () {
    Storage::fake('local');

    [$user, $member] = MemberFixtures::member('PG-MEMBER-PROFILE-STUDENT-PROOF-REPLACE');
    Storage::disk('local')->put('member/student-proofs/old-proof.jpg', 'old-proof');
    $member->forceFill([
        'is_student' => true,
        'student_proof_path' => 'member/student-proofs/old-proof.jpg',
        'student_proof_uploaded_at' => now()->subDay(),
        'student_verification_status' => 'verified',
        'student_verified_at' => now()->subDay(),
        'student_verification_source' => 'admin',
    ])->save();

    $this->actingAs($user)->patch(route('member.profile.update'), [
        'name' => $user->name,
        'email' => $user->email,
        'phone' => $user->phone,
        'gender' => 'male',
        'birth_date' => '2000-01-01',
        'is_student' => '1',
        'student_proof' => UploadedFile::fake()->image('new-proof.png', 480, 320)->size(256),
    ])->assertRedirect(route('member.profile'));

    $member->refresh();
    $newProof = $member->student_proof_path;

    Storage::disk('local')->assertMissing('member/student-proofs/old-proof.jpg');
    Storage::disk('local')->assertExists($newProof);

    $this->actingAs($user)->patch(route('member.profile.update'), [
        'name' => $user->name,
        'email' => $user->email,
        'phone' => $user->phone,
        'gender' => 'male',
        'birth_date' => '2000-01-01',
    ])->assertRedirect(route('member.profile'));

    $member->refresh();

    expect($member->is_student)->toBeFalse()
        ->and($member->student_proof_path)->toBeNull()
        ->and($member->student_proof_uploaded_at)->toBeNull()
        ->and($member->student_verification_status)->toBe('unverified');

    Storage::disk('local')->assertMissing($newProof);
});
