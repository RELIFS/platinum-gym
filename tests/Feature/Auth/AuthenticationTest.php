<?php

use App\Models\Member;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

test('login screen can be rendered', function () {
    $response = $this->get('/login');

    $response->assertStatus(200)
        ->assertSee('brand-logo', false)
        ->assertDontSee('brand-logo-frame', false)
        ->assertSee('data-theme-toggle', false)
        ->assertSee('aria-label="Aktifkan mode gelap"', false)
        ->assertSee('aria-pressed="false"', false);
});

test('login validation uses production indonesian copy', function () {
    $response = $this->post('/login', [
        'email' => '',
        'password' => '',
    ]);

    $response->assertSessionHasErrors([
        'email' => 'Alamat email wajib diisi.',
        'password' => 'Kata sandi wajib diisi.',
    ]);

    $this->assertGuest();
});

test('users can authenticate using the login screen', function () {
    $user = User::factory()->create();
    $user->assignRole('member');
    Member::create([
        'user_id' => $user->id,
        'member_code' => 'PG-AUTH-0001',
        'gender' => 'male',
        'birth_date' => '2000-01-01',
        'joined_at' => now()->toDateString(),
        'status' => 'active',
    ]);

    $response = $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect('/member/dashboard');

    expect($user->fresh()->last_login_at)->not->toBeNull();
});

test('users can not authenticate with invalid password', function () {
    $user = User::factory()->create();

    $response = $this->post('/login', [
        'email' => $user->email,
        'password' => 'wrong-password',
    ]);

    $response->assertSessionHasErrors([
        'email' => 'Email atau kata sandi belum sesuai. Periksa kembali data login Anda.',
    ]);

    $this->assertGuest();
});

test('login throttle message uses production indonesian copy', function () {
    $user = User::factory()->create(['email' => 'throttle@example.com']);
    $key = Str::transliterate(Str::lower($user->email).'|127.0.0.1');

    RateLimiter::clear($key);

    foreach (range(1, 5) as $attempt) {
        $this->post('/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);
    }

    $response = $this->post('/login', [
        'email' => $user->email,
        'password' => 'wrong-password',
    ]);

    $response->assertSessionHasErrors([
        'email' => 'Terlalu banyak percobaan masuk. Coba lagi dalam 1 menit.',
    ]);

    RateLimiter::clear($key);
});

test('users can logout', function () {
    $user = User::factory()->create();
    $user->assignRole('member');

    $response = $this->actingAs($user)->post('/logout');

    $this->assertGuest();
    $response->assertRedirect('/');
});
