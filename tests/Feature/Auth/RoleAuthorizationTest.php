<?php

use App\Models\Member;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

test('login redirects each role to its dashboard', function (string $role, string $path) {
    $user = User::factory()->create();
    $user->assignRole($role);

    if ($role === 'member') {
        Member::create([
            'user_id' => $user->id,
            'member_code' => 'PG-ROLE-0001',
            'gender' => 'male',
            'birth_date' => '2000-01-01',
            'joined_at' => now()->toDateString(),
            'status' => 'active',
        ]);
    }

    $response = $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $this->assertAuthenticatedAs($user);
    $response->assertRedirect($path);
    expect($user->fresh()->last_login_at)->not->toBeNull();
})->with([
    ['member', '/member/dashboard'],
    ['admin', '/admin'],
    ['owner', '/owner'],
]);

test('member without complete profile is redirected to complete profile', function () {
    $member = User::factory()->create();
    $member->assignRole('member');

    $this->actingAs($member)->get('/member/dashboard')
        ->assertRedirect('/member/complete-profile');
});

test('role guarded dashboards allow only matching role', function () {
    $member = User::factory()->create();
    $member->assignRole('member');
    Member::create([
        'user_id' => $member->id,
        'member_code' => 'PG-ROLE-0002',
        'gender' => 'male',
        'birth_date' => '2000-01-01',
        'joined_at' => now()->toDateString(),
        'status' => 'active',
    ]);

    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $owner = User::factory()->create();
    $owner->assignRole('owner');

    $this->actingAs($member)->get('/member/dashboard')->assertOk();
    $this->actingAs($member)->get('/admin')->assertForbidden();

    $this->actingAs($admin)->get('/admin')->assertOk();
    $this->actingAs($admin)->get('/owner')->assertForbidden();

    $this->actingAs($owner)->get('/owner')->assertOk();
    $this->actingAs($owner)->get('/admin')->assertForbidden();
});

test('user without valid role is logged out with error', function () {
    $user = User::factory()->create();

    $response = $this->from('/login')->post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $this->assertGuest();
    $response->assertRedirect('/login');
    $response->assertSessionHasErrors('email');
    expect($user->fresh()->last_login_at)->toBeNull();
});

test('role permission seeder is idempotent', function () {
    $this->seed(RolePermissionSeeder::class);
    $this->seed(RolePermissionSeeder::class);

    expect(Role::count())->toBe(3)
        ->and(Permission::count())->toBe(27)
        ->and(Role::findByName('admin')->permissions)->toHaveCount(13)
        ->and(Role::findByName('owner')->permissions)->toHaveCount(3)
        ->and(Role::findByName('member')->permissions)->toHaveCount(11);
});
