<?php

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

test('revoked member qr token is rendered as inactive and cannot be downloaded', function () {
    [$user, $member] = MemberFixtures::member('PG-MEMBER-QR-REVOKED');
    MemberFixtures::activeMembership($member);
    MemberFixtures::qrToken($member, ['is_revoked' => true]);

    $this->actingAs($user)->get(route('member.qr'))
        ->assertOk()
        ->assertSee('QR dicabut')
        ->assertDontSee('Download QR');
});
