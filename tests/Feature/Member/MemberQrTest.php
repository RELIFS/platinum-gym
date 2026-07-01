<?php

use App\Features\Payments\Actions\FulfillPaidPaymentAction;
use App\Models\MemberPackageSession;
use App\Models\Payment;
use App\Models\QrToken;
use Database\Seeders\RolePermissionSeeder;
use Tests\Feature\Member\Support\MemberPortalFixtures as MemberFixtures;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

test('member qr page shows active qr only for current member active access', function () {
    [$user, $member] = MemberFixtures::member('PG-MEMBER-QR-OWN');
    [, $otherMember] = MemberFixtures::member('PG-MEMBER-QR-OTHER');

    MemberFixtures::activeMembership($member);
    $token = MemberFixtures::qrToken($member, ['token' => str_repeat('a', 64)]);
    MemberFixtures::activeMembership($otherMember);
    MemberFixtures::qrToken($otherMember, ['token' => str_repeat('b', 64)]);

    $this->actingAs($user)->get(route('member.qr'))
        ->assertOk()
        ->assertSee('QR Member')
        ->assertSee('QR aktif')
        ->assertSee('Download QR')
        ->assertDontSee($token->token)
        ->assertDontSee(str_repeat('b', 64));
});

test('member qr download is blocked when member has no active membership', function () {
    [$user, $member] = MemberFixtures::member('PG-MEMBER-QR-NO-ACTIVE');
    MemberFixtures::qrToken($member);

    $this->actingAs($user)->from(route('member.qr'))->get(route('member.qr.download'))
        ->assertRedirect(route('member.qr'))
        ->assertSessionHas('status_kind', 'error');
});

test('member qr is active and downloadable for standalone package session', function () {
    [$user, $member] = MemberFixtures::member('PG-MEMBER-QR-SESSION');
    $package = MemberFixtures::package([
        'name' => 'Poundfit QR Session',
        'slug' => 'poundfit-qr-session',
        'package_kind' => 'session',
        'type' => 'poundfit',
        'session_count' => 4,
    ]);

    MemberPackageSession::create([
        'member_id' => $member->id,
        'package_id' => $package->id,
        'code' => 'MPS-MEMBER-QR-SESSION',
        'total_sessions' => 4,
        'used_sessions' => 0,
        'remaining_sessions' => 4,
        'price' => 200000,
        'started_at' => now()->subDay()->toDateString(),
        'expired_at' => now()->addMonth()->toDateString(),
        'status' => 'active',
    ]);
    MemberFixtures::qrToken($member);

    $this->actingAs($user)->get(route('member.qr'))
        ->assertOk()
        ->assertSee('Aktif untuk sesi')
        ->assertSee('Paket sesi aktif')
        ->assertSee('Download QR');

    $this->actingAs($user)->get(route('member.qr.download'))
        ->assertOk();
});

test('paid standalone package session fulfillment issues member qr token', function () {
    [, $member] = MemberFixtures::member('PG-MEMBER-QR-SESSION-PAID');
    $package = MemberFixtures::package([
        'name' => 'Muaythai QR Paid Session',
        'slug' => 'muaythai-qr-paid-session',
        'package_kind' => 'session',
        'type' => 'muaythai',
        'session_count' => 4,
    ]);
    $packageSession = MemberPackageSession::create([
        'member_id' => $member->id,
        'package_id' => $package->id,
        'code' => 'MPS-MEMBER-QR-PAID',
        'total_sessions' => 4,
        'used_sessions' => 0,
        'remaining_sessions' => 4,
        'price' => 200000,
        'status' => 'pending_payment',
    ]);
    $payment = Payment::create([
        'payment_code' => 'PAY-MEMBER-QR-PAID',
        'member_id' => $member->id,
        'payable_type' => MemberPackageSession::class,
        'payable_id' => $packageSession->id,
        'method' => 'cash',
        'amount' => 200000,
        'status' => 'waiting_confirmation',
    ]);

    app(FulfillPaidPaymentAction::class)->handle($payment);

    expect(QrToken::query()
        ->where('tokenable_id', $member->id)
        ->where('purpose', 'member')
        ->where('is_revoked', false)
        ->exists())->toBeTrue();
});

test('member qr is active for paid membership awaiting first check in', function () {
    [$user, $member] = MemberFixtures::member('PG-MEMBER-QR-AWAITING');
    MemberFixtures::activeMembership($member, overrides: [
        'start_date' => null,
        'end_date' => null,
        'duration_days_snapshot' => 30,
        'activated_at' => now(),
    ]);
    MemberFixtures::qrToken($member);

    $this->actingAs($user)->get(route('member.qr'))
        ->assertOk()
        ->assertSee('QR aktif')
        ->assertSee('Download QR');

    $this->actingAs($user)->get(route('member.qr.download'))
        ->assertOk();
});

test('revoked member qr token is rendered as inactive and cannot be downloaded', function () {
    [$user, $member] = MemberFixtures::member('PG-MEMBER-QR-REVOKED');
    MemberFixtures::activeMembership($member);
    MemberFixtures::qrToken($member, ['is_revoked' => true]);

    $this->actingAs($user)->get(route('member.qr'))
        ->assertOk()
        ->assertSee('QR dicabut')
        ->assertDontSee('Download QR');
});
