<?php

use Database\Seeders\RolePermissionSeeder;
use Tests\Feature\Member\Support\MemberPortalFixtures as MemberFixtures;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

test('member transaction list shows only current member payments', function () {
    [$user, $member] = MemberFixtures::member('PG-MEMBER-TRX-OWN');
    [, $otherMember] = MemberFixtures::member('PG-MEMBER-TRX-OTHER');

    MemberFixtures::payment($member, null, ['payment_code' => 'PAY-TRX-OWN']);
    MemberFixtures::payment($otherMember, null, ['payment_code' => 'PAY-TRX-OTHER']);

    $this->actingAs($user)->get(route('member.transactions'))
        ->assertOk()
        ->assertSee('PAY-TRX-OWN')
        ->assertDontSee('PAY-TRX-OTHER')
        ->assertSee('name="q"', false)
        ->assertSee('name="status"', false);
});

test('member cannot view or pay another member transaction', function () {
    [$user] = MemberFixtures::member('PG-MEMBER-TRX-ACTOR');
    [, $otherMember] = MemberFixtures::member('PG-MEMBER-TRX-TARGET');
    $payment = MemberFixtures::payment($otherMember, null, ['status' => 'waiting_payment']);

    $this->actingAs($user)->get(route('member.transactions.show', $payment))->assertForbidden();
    $this->actingAs($user)->post(route('member.transactions.pay', $payment))->assertForbidden();
});

test('member transaction detail hides payment provider sensitive fields', function () {
    [$user, $member] = MemberFixtures::member('PG-MEMBER-TRX-SENSITIVE');
    $payment = MemberFixtures::sensitivePayment($member);

    $this->actingAs($user)->get(route('member.transactions.show', $payment))
        ->assertOk()
        ->assertSee($payment->payment_code)
        ->assertDontSee('secret-snap-token-member')
        ->assertDontSee('payment.example.test/member-secret')
        ->assertDontSee('raw-secret-member')
        ->assertDontSee('Catatan internal member');
});
