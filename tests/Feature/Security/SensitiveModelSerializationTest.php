<?php

use App\Models\AccountInvitation;
use App\Models\Member;
use App\Models\Payment;
use App\Models\QrToken;
use App\Models\SocialAccount;
use App\Models\User;

test('sensitive payment oauth and qr attributes are hidden from model serialization', function () {
    $user = User::factory()->create();

    $member = Member::create([
        'user_id' => $user->id,
        'member_code' => 'PG-SAFE-0001',
        'gender' => 'male',
        'birth_date' => '2000-01-01',
        'joined_at' => now()->toDateString(),
        'status' => 'active',
    ]);

    $payment = Payment::create([
        'payment_code' => 'PAY-SAFE-0001',
        'member_id' => $member->id,
        'payable_type' => Member::class,
        'payable_id' => $member->id,
        'method' => 'midtrans',
        'amount' => 250000,
        'status' => 'waiting_payment',
        'midtrans_snap_token' => 'secret-snap-token',
        'midtrans_redirect_url' => 'https://app.sandbox.midtrans.com/snap/v4/redirection/secret-snap-token',
        'midtrans_raw_response' => ['token' => 'raw-secret-token'],
    ]);

    $socialAccount = SocialAccount::create([
        'user_id' => $user->id,
        'provider' => 'google',
        'provider_user_id' => 'google-safe-serialization',
        'provider_email' => 'safe@example.test',
        'provider_avatar' => 'https://example.test/avatar.png',
        'access_token' => 'oauth-access-token',
        'refresh_token' => 'oauth-refresh-token',
    ]);

    $qrToken = QrToken::create([
        'tokenable_type' => Member::class,
        'tokenable_id' => $member->id,
        'token' => str_repeat('a', 64),
        'purpose' => 'member',
    ]);

    $invitation = AccountInvitation::create([
        'user_id' => $user->id,
        'created_by' => $user->id,
        'token_hash' => hash('sha256', 'plain-invitation-token'),
        'expires_at' => now()->addDay(),
    ]);

    expect($payment->toArray())
        ->not->toHaveKeys(['midtrans_snap_token', 'midtrans_redirect_url', 'midtrans_raw_response'])
        ->and($socialAccount->toArray())
        ->not->toHaveKeys(['access_token', 'refresh_token'])
        ->and($qrToken->toArray())
        ->not->toHaveKey('token')
        ->and($invitation->toArray())
        ->not->toHaveKey('token_hash');
});
