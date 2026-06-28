<?php

use Database\Seeders\RolePermissionSeeder;
use Tests\Feature\Owner\Support\OwnerPortalFixtures as OwnerFixtures;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

test('member report shows only memberships overlapping period and matching filters', function () {
    $owner = OwnerFixtures::owner();
    [, $member] = OwnerFixtures::member('PG-OWN-MEMBER-A');
    [, $otherMember] = OwnerFixtures::member('PG-OWN-MEMBER-B');
    $targetPackage = OwnerFixtures::package(['name' => 'Paket Member Owner Target']);
    $otherPackage = OwnerFixtures::package(['name' => 'Paket Member Owner Lain']);

    OwnerFixtures::membership($member, $targetPackage, [
        'code' => 'MBR-OWN-MEMBER-TARGET',
        'status' => 'active',
        'start_date' => now()->subWeek()->toDateString(),
        'end_date' => now()->addMonth()->toDateString(),
    ]);
    OwnerFixtures::membership($otherMember, $otherPackage, [
        'code' => 'MBR-OWN-MEMBER-OTHER',
        'status' => 'inactive',
        'start_date' => now()->subMonths(3)->toDateString(),
        'end_date' => now()->subMonths(2)->toDateString(),
    ]);

    $this->actingAs($owner)->get(route('owner.reports.members', [
        'date_from' => now()->subMonth()->toDateString(),
        'date_to' => now()->toDateString(),
        'status' => 'active',
        'package_id' => $targetPackage->id,
        'q' => 'MBR-OWN-MEMBER',
    ]))->assertOk()
        ->assertSee('Laporan Member & Membership')
        ->assertSee('MBR-OWN-MEMBER-TARGET')
        ->assertSee('Paket Member Owner Target')
        ->assertSee('Aktif')
        ->assertDontSee('MBR-OWN-MEMBER-OTHER')
        ->assertDontSee('Paket Member Owner Lain');
});
