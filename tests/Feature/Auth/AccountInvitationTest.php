<?php

use App\Models\AccountInvitation;
use App\Models\Member;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Support\Facades\Hash;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

test('member can activate invited account with a valid one time token', function () {
    $user = User::factory()->unverified()->create([
        'password' => Hash::make('temporary-random-password'),
    ]);
    $user->assignRole('member');
    Member::create([
        'user_id' => $user->id,
        'member_code' => 'PG-INVITE-0001',
        'gender' => 'male',
        'birth_date' => '2000-01-01',
        'joined_at' => now()->toDateString(),
        'status' => 'active',
    ]);

    $token = 'valid-invitation-token';
    $invitation = AccountInvitation::create([
        'user_id' => $user->id,
        'token_hash' => hash('sha256', $token),
        'expires_at' => now()->addHours(2),
        'sent_at' => now(),
    ]);

    $this->get(route('account-invitations.accept', $token))
        ->assertOk()
        ->assertSee('Aktivasi')
        ->assertSee('name="password"', false);

    $this->post(route('account-invitations.store', $token), [
        'password' => 'password-baru-aman',
        'password_confirmation' => 'password-baru-aman',
    ])
        ->assertRedirect('/member/dashboard')
        ->assertSessionHas('status', 'Akun berhasil diaktifkan. Selamat datang di Platinum Gym Padang.');

    expect($user->fresh()->hasVerifiedEmail())->toBeTrue()
        ->and(Hash::check('password-baru-aman', $user->fresh()->password))->toBeTrue()
        ->and($invitation->fresh()->accepted_at)->not->toBeNull();
});

test('expired account invitation cannot be accepted', function () {
    $user = User::factory()->unverified()->create();
    $user->assignRole('member');
    $token = 'expired-invitation-token';

    AccountInvitation::create([
        'user_id' => $user->id,
        'token_hash' => hash('sha256', $token),
        'expires_at' => now()->subMinute(),
        'sent_at' => now()->subDay(),
    ]);

    $this->get(route('account-invitations.accept', $token))
        ->assertNotFound();

    $this->post(route('account-invitations.store', $token), [
        'password' => 'password-baru-aman',
        'password_confirmation' => 'password-baru-aman',
    ])
        ->assertSessionHasErrors(['token' => 'Undangan akun sudah tidak berlaku. Minta admin mengirim ulang undangan.']);
});
