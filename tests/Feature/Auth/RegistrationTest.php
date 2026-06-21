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

    $response->assertStatus(200)
        ->assertSee('name="birth_date_display"', false)
        ->assertSee('name="birth_date"', false)
        ->assertSee('placeholder="dd/mm/yyyy"', false)
        ->assertSee('aria-label="Pilih tanggal lahir"', false)
        ->assertDontSee('Pilih tanggal lahir sesuai identitas.')
        ->assertDontSee('name="birth_day"', false)
        ->assertDontSee('name="birth_month"', false)
        ->assertDontSee('name="birth_year"', false);
});

test('new members can register with dd mm yyyy birth date display', function () {
    Notification::fake();

    $this->post('/register', [
        'name' => 'Display Date Member',
        'birth_date_display' => '15/01/2000',
        'gender' => 'male',
        'phone' => '081234567894',
        'email' => 'display-date@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'terms' => '1',
    ])->assertRedirect(route('verification.notice', absolute: false));

    $member = User::where('email', 'display-date@example.com')->firstOrFail()->member;

    expect($member->birth_date->toDateString())->toBe('2000-01-15');
});

test('new members can register with separated birth date fields', function () {
    Notification::fake();

    $this->post('/register', [
        'name' => 'Tanggal Member',
        'birth_day' => '15',
        'birth_month' => '1',
        'birth_year' => '2000',
        'gender' => 'male',
        'phone' => '081234567892',
        'email' => 'tanggal@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'terms' => '1',
    ])->assertRedirect(route('verification.notice', absolute: false));

    $member = User::where('email', 'tanggal@example.com')->firstOrFail()->member;

    expect($member->birth_date->toDateString())->toBe('2000-01-15');
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

test('registration rejects invalid separated birth date fields', function () {
    $response = $this->post('/register', [
        'name' => 'Invalid Date Member',
        'birth_day' => '31',
        'birth_month' => '2',
        'birth_year' => '2000',
        'gender' => 'male',
        'phone' => '081234567893',
        'email' => 'invalid-date@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'terms' => '1',
    ]);

    $response->assertSessionHasErrors(['birth_date']);
    $this->assertGuest();
});

test('registration rejects invalid dd mm yyyy birth date display', function () {
    $response = $this->post('/register', [
        'name' => 'Invalid Display Member',
        'birth_date_display' => '31/02/2000',
        'gender' => 'male',
        'phone' => '081234567895',
        'email' => 'invalid-display@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'terms' => '1',
    ]);

    $response->assertSessionHasErrors(['birth_date']);
    $this->assertGuest();
});
