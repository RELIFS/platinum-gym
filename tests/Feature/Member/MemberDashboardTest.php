<?php

use Database\Seeders\RolePermissionSeeder;
use Tests\Feature\Member\Support\MemberPortalFixtures as MemberFixtures;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

test('member dashboard renders the member shell and accessible navigation landmarks', function () {
    [$user] = MemberFixtures::member('PG-MEMBER-DASH-SHELL');

    $this->actingAs($user)->get(route('member.dashboard'))
        ->assertOk()
        ->assertSee('Lewati navigasi member')
        ->assertSee('id="member-main"', false)
        ->assertSee('aria-label="Navigasi member"', false)
        ->assertSee('id="member-mobile-navigation"', false)
        ->assertSee('role="dialog"', false)
        ->assertSee('aria-controls="member-mobile-navigation"', false)
        ->assertSee('Dashboard Member')
        ->assertSee('Gymmi')
        ->assertSee('data-chatbot-panel hidden', false)
        ->assertDontSee('Dashboard Admin')
        ->assertDontSee('Owner Portal');
});

test('member dashboard shows own operational data and hides another member data', function () {
    [$user, $member] = MemberFixtures::member('PG-MEMBER-DASH-OWN');
    [, $otherMember] = MemberFixtures::member('PG-MEMBER-DASH-OTHER');

    $ownPackage = MemberFixtures::package(['name' => 'Gym Own Dashboard']);
    $otherPackage = MemberFixtures::package(['name' => 'Gym Other Dashboard']);
    $ownMembership = MemberFixtures::activeMembership($member, $ownPackage);
    $otherMembership = MemberFixtures::activeMembership($otherMember, $otherPackage);

    MemberFixtures::payment($member, $ownMembership, ['payment_code' => 'PAY-DASH-OWN']);
    MemberFixtures::payment($otherMember, $otherMembership, ['payment_code' => 'PAY-DASH-OTHER']);

    $this->actingAs($user)->get(route('member.dashboard'))
        ->assertOk()
        ->assertSee('Gym Own Dashboard')
        ->assertSee('PAY-DASH-OWN')
        ->assertDontSee('Gym Other Dashboard')
        ->assertDontSee('PAY-DASH-OTHER');
});
