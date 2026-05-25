<?php

use App\Models\Member;
use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Support\Facades\Notification;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Role::findOrCreate('member', 'web');
});

test('registration screen can be rendered', function () {
    $response = $this->get('/register');

    $response->assertStatus(200);
});

test('new members can register', function () {
    Notification::fake();

    $response = $this->post('/register', [
        'name' => 'Test Member',
        'birth_date' => '2000-01-15',
        'gender' => 'male',
        'phone' => '081234567891',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'terms' => '1',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('verification.notice', absolute: false));

    $user = User::where('email', 'test@example.com')->first();

    expect($user)->not->toBeNull()
        ->and($user->phone)->toBe('081234567891')
        ->and($user->hasVerifiedEmail())->toBeFalse()
        ->and($user->hasRole('member'))->toBeTrue();

    $member = Member::where('user_id', $user->id)->first();

    expect($member)->not->toBeNull()
        ->and($member->member_code)->toStartWith('PG-'.now()->format('Ymd').'-')
        ->and($member->gender)->toBe('male')
        ->and($member->birth_date->toDateString())->toBe('2000-01-15')
        ->and($member->status)->toBe('active');

    Notification::assertSentTo($user, VerifyEmail::class);
});

test('registration requires member fields and terms', function () {
    $response = $this->post('/register', [
        'name' => 'Test Member',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $response->assertSessionHasErrors(['birth_date', 'gender', 'phone', 'terms']);
    $this->assertGuest();
});

test('registration normalizes indonesian whatsapp number', function () {
    $this->post('/register', [
        'name' => 'Test Member',
        'birth_date' => '2000-01-15',
        'gender' => 'female',
        'phone' => '+62 812-3456-7891',
        'email' => 'normalized@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'terms' => '1',
    ]);

    expect(User::where('email', 'normalized@example.com')->value('phone'))->toBe('081234567891');
});

test('registration rejects duplicate whatsapp number', function () {
    User::factory()->create(['phone' => '081234567891']);

    $response = $this->post('/register', [
        'name' => 'Test Member',
        'birth_date' => '2000-01-15',
        'gender' => 'male',
        'phone' => '081234567891',
        'email' => 'duplicate-phone@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'terms' => '1',
    ]);

    $response->assertSessionHasErrors('phone');
    $this->assertGuest();
});

test('registration rejects invalid member fields', function () {
    $response = $this->post('/register', [
        'name' => 'Test Member',
        'birth_date' => now()->addDay()->toDateString(),
        'gender' => 'other',
        'phone' => '12345',
        'email' => 'invalid-fields@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'terms' => '1',
    ]);

    $response->assertSessionHasErrors(['birth_date', 'gender', 'phone']);
    $this->assertGuest();
});
