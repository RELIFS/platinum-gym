<?php

use App\Features\Auth\Support\MaskedEmail;
use App\Models\EmailVerificationCode;
use App\Models\Member;
use App\Models\User;
use App\Notifications\Auth\EmailVerificationCodeNotification;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Auth\Events\Verified;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

test('email verification screen can be rendered', function () {
    $user = User::factory()->unverified()->create([
        'email' => 'luthfi.member@example.com',
    ]);
    $user->assignRole('member');

    $response = $this->actingAs($user)->get('/verify-email');

    $response->assertStatus(200);
    $response->assertSee('name="code"', false)
        ->assertSee('autocomplete="one-time-code"', false)
        ->assertSee('lut**********@example.com')
        ->assertDontSee('luthfi.member@example.com')
        ->assertDontSee('Email tujuan')
        ->assertDontSee('Cek folder')
        ->assertSee('Masukkan kode 6 digit yang dikirim ke')
        ->assertSee('Kode berlaku 10 menit.')
        ->assertSee('Jangan bagikan kode ini kepada siapa pun')
        ->assertSee('Aktifkan Akun')
        ->assertSee('Belum menerima kode? Cek Inbox, Spam, atau Promosi')
        ->assertSee('Kode terbaru akan menggantikan kode sebelumnya.')
        ->assertDontSee('Kode lama otomatis diganti dengan kode terbaru')
        ->assertDontSee('Kode berlaku 10 menit. Jangan bagikan kode ini kepada siapa pun');
});

test('email masking keeps domain visible and hides local part safely', function (string $email, string $expected) {
    expect(MaskedEmail::forDisplay($email))->toBe($expected);
})->with([
    'standard email' => ['luthfi@gmail.com', 'lut***@gmail.com'],
    'short local part' => ['ab@gmail.com', 'a*@gmail.com'],
    'plus alias' => ['member+qa@gmail.com', 'mem******@gmail.com'],
    'malformed value' => ['invalid-email', 'i************'],
]);

test('unverified users are redirected away from dashboard', function () {
    $user = User::factory()->unverified()->create();
    $user->assignRole('member');

    $response = $this->actingAs($user)->get('/dashboard');

    $response->assertRedirect(route('verification.notice', absolute: false));
});

test('unverified users are redirected away from member dashboard', function () {
    $user = User::factory()->unverified()->create();
    $user->assignRole('member');

    $response = $this->actingAs($user)->get('/member/dashboard');

    $response->assertRedirect(route('verification.notice', absolute: false));
});

test('verified users are redirected from generic dashboard to role dashboard', function () {
    $user = User::factory()->create();
    $user->assignRole('member');
    Member::create([
        'user_id' => $user->id,
        'member_code' => 'PG-VERIFY-0001',
        'gender' => 'male',
        'birth_date' => '2000-01-01',
        'joined_at' => now()->toDateString(),
        'status' => 'active',
    ]);

    $response = $this->actingAs($user)->get('/dashboard');

    $response->assertRedirect('/member/dashboard');
});

test('email can be verified', function () {
    $user = User::factory()->unverified()->create();
    $user->assignRole('member');
    Member::create([
        'user_id' => $user->id,
        'member_code' => 'PG-VERIFY-0002',
        'gender' => 'male',
        'birth_date' => '2000-01-01',
        'joined_at' => now()->toDateString(),
        'status' => 'active',
    ]);

    Event::fake();

    $verificationUrl = URL::temporarySignedRoute(
        'verification.verify',
        now()->addMinutes(60),
        ['id' => $user->id, 'hash' => sha1($user->email)]
    );

    $response = $this->actingAs($user)->get($verificationUrl);

    Event::assertDispatched(Verified::class);
    expect($user->fresh()->hasVerifiedEmail())->toBeTrue();
    $response->assertRedirect('/member/dashboard?verified=1');
});

test('email can be verified with verification code', function () {
    Notification::fake();
    Event::fake();
    $user = User::factory()->unverified()->create();
    $user->assignRole('member');
    Member::create([
        'user_id' => $user->id,
        'member_code' => 'PG-VERIFY-CODE',
        'gender' => 'male',
        'birth_date' => '2000-01-01',
        'joined_at' => now()->toDateString(),
        'status' => 'active',
    ]);

    $user->sendEmailVerificationNotification();
    $sentCode = null;
    Notification::assertSentTo($user, EmailVerificationCodeNotification::class, function (EmailVerificationCodeNotification $notification) use (&$sentCode, $user): bool {
        $sentCode = $notification->code;

        return strlen($notification->code) === 6
            && str_contains($notification->verificationUrl, '/verify-email/')
            && $notification->maskedEmail === MaskedEmail::forDisplay($user->email)
            && str_contains($notification->toMail($user)->render(), 'Kode verifikasi')
            && str_contains($notification->toMail($user)->render(), 'Verifikasi Email');
    });

    $response = $this->actingAs($user)->post(route('verification.code.verify'), [
        'code' => $sentCode,
    ]);

    Event::assertDispatched(Verified::class);
    expect($user->fresh()->hasVerifiedEmail())->toBeTrue();
    $response->assertRedirect('/member/dashboard?verified=1');
});

test('invalid email verification code is rejected and counted', function () {
    Notification::fake();
    $user = User::factory()->unverified()->create();
    $user->assignRole('member');
    $user->sendEmailVerificationNotification();

    $response = $this->actingAs($user)->from(route('verification.notice'))->post(route('verification.code.verify'), [
        'code' => '000000',
    ]);

    $response->assertRedirect(route('verification.notice'))
        ->assertSessionHasErrors(['code' => 'Kode verifikasi belum sesuai.']);

    expect($user->fresh()->hasVerifiedEmail())->toBeFalse()
        ->and(EmailVerificationCode::query()->whereBelongsTo($user)->value('attempts'))->toBe(1);
});

test('email is not verified with invalid hash', function () {
    $user = User::factory()->unverified()->create();
    $user->assignRole('member');

    $verificationUrl = URL::temporarySignedRoute(
        'verification.verify',
        now()->addMinutes(60),
        ['id' => $user->id, 'hash' => sha1('wrong-email')]
    );

    $this->actingAs($user)->get($verificationUrl);

    expect($user->fresh()->hasVerifiedEmail())->toBeFalse();
});

test('verification email can be resent', function () {
    Notification::fake();

    $user = User::factory()->unverified()->create();
    $user->assignRole('member');

    $response = $this->actingAs($user)->post('/email/verification-notification');

    $response->assertSessionHas('status', 'verification-code-sent');
    Notification::assertSentTo($user, EmailVerificationCodeNotification::class);
    expect(EmailVerificationCode::query()->whereBelongsTo($user)->count())->toBe(1);
});
