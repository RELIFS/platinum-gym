<?php

use App\Models\Member;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Support\Facades\Notification;
use Tests\Feature\Admin\Support\AdminPortalFixtures as AdminFixture;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

test('admin can create member resource with member role and profile record', function () {
    Notification::fake();
    $admin = AdminFixture::admin();

    $this->actingAs($admin)
        ->post(route('admin.resources.store', 'members'), [
            'name' => 'Member Resource QA',
            'email' => 'member.resource.qa@gmail.com',
            'phone' => '081299990001',
            'gender' => 'female',
            'birth_date' => '2001-01-01',
            'address' => 'Padang',
            'emergency_contact' => '081299990002',
            'is_student' => '1',
            'student_id_number' => 'QA12345',
            'student_verification_status' => 'verified',
            'status' => 'active',
        ])
        ->assertRedirect(route('admin.members'));

    $user = User::query()->where('email', 'member.resource.qa@gmail.com')->firstOrFail();
    $member = Member::query()->where('user_id', $user->id)->firstOrFail();

    expect($user->hasRole('member'))->toBeTrue()
        ->and($member->gender)->toBe('female')
        ->and($member->student_verification_status)->toBe('verified')
        ->and($member->student_verified_at)->not->toBeNull();
});

test('admin member resource protects unique user email validation', function () {
    $admin = AdminFixture::admin();
    AdminFixture::member(userOverrides: ['email' => 'duplicate.member@gmail.com']);

    $this->actingAs($admin)
        ->from(route('admin.resources.create', 'members'))
        ->post(route('admin.resources.store', 'members'), [
            'name' => 'Duplicate Member',
            'email' => 'duplicate.member@gmail.com',
            'gender' => 'male',
            'status' => 'active',
        ])
        ->assertRedirect(route('admin.resources.create', 'members'))
        ->assertSessionHasErrors('email');
});
