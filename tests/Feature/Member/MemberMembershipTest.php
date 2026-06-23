<?php

use Database\Seeders\RolePermissionSeeder;
use Tests\Feature\Member\Support\MemberPortalFixtures as MemberFixtures;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

test('member membership page renders catalog controls and active membership rail', function () {
    [$user, $member] = MemberFixtures::member('PG-MEMBER-MEMBERSHIP');
    $package = MemberFixtures::package(['name' => 'Gym Membership Aktif Member']);

    MemberFixtures::activeMembership($member, $package);
    MemberFixtures::package(['name' => 'Paket Katalog Member']);

    $this->actingAs($user)->get(route('member.membership'))
        ->assertOk()
        ->assertSee('Membership')
        ->assertSee('Daftar membership aktif')
        ->assertSee('Gym Membership Aktif Member')
        ->assertSee('Paket Katalog Member')
        ->assertSee('name="q"', false)
        ->assertSee('name="kind"', false)
        ->assertSee('member-status-pill', false);
});

test('member membership page does not render another member active package as own active rail', function () {
    [$user] = MemberFixtures::member('PG-MEMBER-MEMBERSHIP-OWN');
    [, $otherMember] = MemberFixtures::member('PG-MEMBER-MEMBERSHIP-OTHER');

    $otherPackage = MemberFixtures::package([
        'name' => 'Membership Aktif Milik Orang Lain',
        'is_active' => false,
    ]);
    MemberFixtures::activeMembership($otherMember, $otherPackage);

    $this->actingAs($user)->get(route('member.membership'))
        ->assertOk()
        ->assertDontSee('Daftar membership aktif')
        ->assertDontSee('Membership Aktif Milik Orang Lain');
});
