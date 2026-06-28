<?php

use App\Models\Member;
use App\Models\SocialAccount;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);

    config([
        'services.google.client_id' => 'google-client-id',
        'services.google.client_secret' => 'google-client-secret',
        'services.google.redirect' => 'http://localhost/auth/google/callback',
    ]);
});

function platinumGoogleUser(string $id = 'google-123', string $email = 'member@example.com', string $name = 'Google Member'): SocialiteUser
{
    return (new SocialiteUser)
        ->setRaw([
            'sub' => $id,
            'email' => $email,
            'name' => $name,
            'picture' => 'https://example.com/avatar.jpg',
        ])
        ->map([
            'id' => $id,
            'email' => $email,
            'name' => $name,
            'avatar' => 'https://example.com/avatar.jpg',
        ])
        ->setToken('google-access-token')
        ->setRefreshToken('google-refresh-token')
        ->setExpiresIn(3600);
}

function createCompleteMember(User $user, string $code): Member
{
    return Member::create([
        'user_id' => $user->id,
        'member_code' => $code,
        'gender' => 'male',
        'birth_date' => '2000-01-01',
        'joined_at' => now()->toDateString(),
        'status' => 'active',
    ]);
}

test('login and register pages render google auth button', function () {
    $this->get('/login')
        ->assertOk()
        ->assertSee('Masuk dengan Google');

    $this->get('/register')
        ->assertOk()
        ->assertSee('Daftar dengan Google');
});

test('google redirect route calls socialite google driver', function () {
    $provider = Mockery::mock();
    $provider->shouldReceive('redirect')
        ->once()
        ->andReturn(redirect()->away('https://accounts.google.com/o/oauth2/v2/auth'));

    Socialite::shouldReceive('driver')
        ->once()
        ->with('google')
        ->andReturn($provider);

    $this->get('/auth/google/redirect')
        ->assertRedirect('https://accounts.google.com/o/oauth2/v2/auth');
});

test('google callback logs in existing linked social account', function () {
    $user = User::factory()->create(['email' => 'linked@example.com']);
    $user->assignRole('member');
    createCompleteMember($user, 'PG-GOOGLE-0001');

    SocialAccount::create([
        'user_id' => $user->id,
        'provider' => 'google',
        'provider_user_id' => 'google-linked',
        'provider_email' => 'linked@example.com',
    ]);

    $provider = Mockery::mock();
    $provider->shouldReceive('stateless')->once()->andReturnSelf();
    $provider->shouldReceive('user')->once()->andReturn(platinumGoogleUser('google-linked', 'linked@example.com'));

    Socialite::shouldReceive('driver')->once()->with('google')->andReturn($provider);

    $response = $this->get('/auth/google/callback?code=test-code');

    $this->assertAuthenticatedAs($user);
    $response->assertRedirect('/member/dashboard');
    expect($user->fresh()->last_login_at)->not->toBeNull()
        ->and($user->socialAccounts()->where('provider', 'google')->first()->access_token)->toBe('google-access-token');
});

test('google callback links existing local user by email', function () {
    $user = User::factory()->create(['email' => 'local@example.com']);
    $user->assignRole('member');
    createCompleteMember($user, 'PG-GOOGLE-0002');

    $provider = Mockery::mock();
    $provider->shouldReceive('stateless')->once()->andReturnSelf();
    $provider->shouldReceive('user')->once()->andReturn(platinumGoogleUser('google-local', 'local@example.com'));

    Socialite::shouldReceive('driver')->once()->with('google')->andReturn($provider);

    $response = $this->get('/auth/google/callback?code=test-code');

    $this->assertAuthenticatedAs($user);
    $response->assertRedirect('/member/dashboard');
    expect(SocialAccount::where('provider_user_id', 'google-local')->where('user_id', $user->id)->exists())->toBeTrue();
});

test('google callback creates new verified member user and redirects to complete profile', function () {
    $provider = Mockery::mock();
    $provider->shouldReceive('stateless')->once()->andReturnSelf();
    $provider->shouldReceive('user')->once()->andReturn(platinumGoogleUser('google-new', 'new-google@example.com', 'New Google'));

    Socialite::shouldReceive('driver')->once()->with('google')->andReturn($provider);

    $response = $this->get('/auth/google/callback?code=test-code');

    $user = User::where('email', 'new-google@example.com')->first();

    expect($user)->not->toBeNull()
        ->and($user->hasVerifiedEmail())->toBeTrue()
        ->and($user->hasRole('member'))->toBeTrue()
        ->and($user->member)->toBeNull();

    $this->assertAuthenticatedAs($user);
    $response->assertRedirect('/member/complete-profile');
    expect(SocialAccount::where('provider_user_id', 'google-new')->where('user_id', $user->id)->exists())->toBeTrue();
});

test('complete profile screen can be rendered for google member onboarding', function () {
    $user = User::factory()->create([
        'email_verified_at' => now(),
        'phone' => null,
    ]);
    $user->assignRole('member');

    $this->actingAs($user)->get('/member/complete-profile')
        ->assertOk()
        ->assertSee('Lengkapi')
        ->assertSee('Profil')
        ->assertSee('name="birth_date_display"', false)
        ->assertSee('name="birth_date"', false)
        ->assertSee('placeholder="dd/mm/yyyy"', false)
        ->assertSee('x-modelable="isoValue"', false)
        ->assertSee('aria-label="Pilih tanggal lahir"', false)
        ->assertSee('auth-terms-row', false)
        ->assertSee('auth-terms-checkbox', false)
        ->assertSee('auth-inline-link', false)
        ->assertDontSee('Pilih tanggal lahir sesuai identitas.')
        ->assertDontSee('class="auth-link">Syarat &amp; Ketentuan', false)
        ->assertDontSee('name="birth_day"', false)
        ->assertDontSee('name="birth_month"', false)
        ->assertDontSee('name="birth_year"', false)
        ->assertSee('name="gender"', false)
        ->assertSee('name="phone"', false)
        ->assertSee('name="terms"', false);
});

test('complete profile accepts dd mm yyyy birth date display', function () {
    $user = User::factory()->create([
        'email' => 'complete-display@example.com',
        'email_verified_at' => now(),
        'phone' => null,
    ]);
    $user->assignRole('member');

    $this->actingAs($user)->post('/member/complete-profile', [
        'birth_date_display' => '15/01/2000',
        'gender' => 'female',
        'phone' => '+62 812-3456-7895',
        'terms' => '1',
    ])->assertRedirect('/member/dashboard');

    expect($user->fresh()->member)->not->toBeNull()
        ->and($user->member->birth_date->toDateString())->toBe('2000-01-15');
});

test('complete profile accepts separated birth date fields', function () {
    $user = User::factory()->create([
        'email' => 'complete-parts@example.com',
        'email_verified_at' => now(),
        'phone' => null,
    ]);
    $user->assignRole('member');

    $this->actingAs($user)->post('/member/complete-profile', [
        'birth_day' => '1',
        'birth_month' => '1',
        'birth_year' => '2000',
        'gender' => 'female',
        'phone' => '+62 812-3456-7892',
        'terms' => '1',
    ])->assertRedirect('/member/dashboard');

    expect($user->fresh()->member)->not->toBeNull()
        ->and($user->member->birth_date->toDateString())->toBe('2000-01-01');
});

test('complete profile creates member profile for google user', function () {
    $user = User::factory()->create([
        'email' => 'complete@example.com',
        'email_verified_at' => now(),
        'phone' => null,
    ]);
    $user->assignRole('member');

    $response = $this->actingAs($user)->post('/member/complete-profile', [
        'birth_date' => '2000-01-01',
        'gender' => 'female',
        'phone' => '+62 812-3456-7891',
        'terms' => '1',
    ]);

    $response->assertRedirect('/member/dashboard');

    expect($user->fresh()->phone)->toBe('081234567891')
        ->and($user->member)->not->toBeNull()
        ->and($user->member->gender)->toBe('female');
});

test('complete profile rejects duplicate whatsapp number', function () {
    User::factory()->create(['phone' => '081234567891']);

    $user = User::factory()->create(['email_verified_at' => now(), 'phone' => null]);
    $user->assignRole('member');

    $response = $this->actingAs($user)->post('/member/complete-profile', [
        'birth_date' => '2000-01-01',
        'gender' => 'male',
        'phone' => '081234567891',
        'terms' => '1',
    ]);

    $response->assertSessionHasErrors('phone');
    expect($user->fresh()->member)->toBeNull();
});

test('google callback failure redirects to login with error', function () {
    $provider = Mockery::mock();
    $provider->shouldReceive('stateless')->once()->andReturnSelf();
    $provider->shouldReceive('user')->once()->andThrow(new RuntimeException('cancelled'));

    Socialite::shouldReceive('driver')->once()->with('google')->andReturn($provider);

    $response = $this->get('/auth/google/callback?error=access_denied');

    $response->assertRedirect('/login');
    $response->assertSessionHasErrors('email');
    $this->assertGuest();
});

test('complete profile validation errors do not create member profile', function () {
    $user = User::factory()->create([
        'email_verified_at' => now(),
        'phone' => null,
    ]);
    $user->assignRole('member');

    $response = $this->actingAs($user)->post('/member/complete-profile', [
        'birth_date' => now()->addDay()->toDateString(),
        'gender' => 'other',
        'phone' => '12345',
        'terms' => '1',
    ]);

    $response->assertSessionHasErrors(['birth_date', 'gender', 'phone']);
    expect($user->fresh()->member)->toBeNull();
});

test('complete profile rejects invalid separated birth date fields', function () {
    $user = User::factory()->create([
        'email_verified_at' => now(),
        'phone' => null,
    ]);
    $user->assignRole('member');

    $response = $this->actingAs($user)->post('/member/complete-profile', [
        'birth_day' => '31',
        'birth_month' => '2',
        'birth_year' => '2000',
        'gender' => 'male',
        'phone' => '081234567894',
        'terms' => '1',
    ]);

    $response->assertSessionHasErrors(['birth_date']);
    expect($user->fresh()->member)->toBeNull();
});

test('complete profile rejects invalid dd mm yyyy birth date display', function () {
    $user = User::factory()->create([
        'email_verified_at' => now(),
        'phone' => null,
    ]);
    $user->assignRole('member');

    $response = $this->actingAs($user)->post('/member/complete-profile', [
        'birth_date_display' => '31/02/2000',
        'gender' => 'male',
        'phone' => '081234567896',
        'terms' => '1',
    ]);

    $response->assertSessionHasErrors(['birth_date']);
    expect($user->fresh()->member)->toBeNull();
});
