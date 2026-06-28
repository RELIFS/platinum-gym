<?php

use Database\Seeders\RolePermissionSeeder;
use Tests\Feature\Owner\Support\OwnerPortalFixtures as OwnerFixtures;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

test('booking report shows enrollments matching date class status and search filters', function () {
    $owner = OwnerFixtures::owner();
    [, $member] = OwnerFixtures::member('PG-OWN-BOOKING-A', ['name' => 'Member Booking Target']);
    [, $otherMember] = OwnerFixtures::member('PG-OWN-BOOKING-B', ['name' => 'Member Booking Other']);
    [$gymClass, $schedule] = OwnerFixtures::schedule(now()->toDateString(), ['name' => 'Yoga Owner Target']);
    [, $otherSchedule] = OwnerFixtures::schedule(now()->toDateString(), ['name' => 'Muaythai Owner Other']);

    OwnerFixtures::enrollment($member, $schedule, [
        'session_date' => now()->toDateString(),
        'status' => 'confirmed',
    ]);
    OwnerFixtures::enrollment($otherMember, $otherSchedule, [
        'session_date' => now()->toDateString(),
        'status' => 'booked',
    ]);

    $this->actingAs($owner)->get(route('owner.reports.classes', [
        'date_from' => now()->subDay()->toDateString(),
        'date_to' => now()->addDay()->toDateString(),
        'status' => 'confirmed',
        'class_id' => $gymClass->id,
        'q' => 'Booking Target',
    ]))->assertOk()
        ->assertSee('Laporan Booking & Kelas')
        ->assertSee('Yoga Owner Target')
        ->assertSee('Member Booking Target')
        ->assertSee('Terkonfirmasi')
        ->assertDontSee('Muaythai Owner Other')
        ->assertDontSee('Member Booking Other');
});
