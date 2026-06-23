<?php

use Database\Seeders\RolePermissionSeeder;
use Tests\Feature\Member\Support\MemberPortalFixtures as MemberFixtures;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

test('member booking history lists only own enrollments', function () {
    [$user, $member] = MemberFixtures::member('PG-MEMBER-BOOKING-OWN');
    [, $otherMember] = MemberFixtures::member('PG-MEMBER-BOOKING-OTHER');

    $ownSchedule = MemberFixtures::schedule(['name' => 'Kelas Booking Sendiri']);
    $otherSchedule = MemberFixtures::schedule(['name' => 'Kelas Booking Orang Lain']);

    MemberFixtures::enrollment($member, $ownSchedule);
    MemberFixtures::enrollment($otherMember, $otherSchedule);

    $this->actingAs($user)->get(route('member.bookings'))
        ->assertOk()
        ->assertSee('Kelas Booking Sendiri')
        ->assertDontSee('Kelas Booking Orang Lain');
});

test('member cannot cancel another member booking', function () {
    [$user] = MemberFixtures::member('PG-MEMBER-BOOKING-CANCEL-OWN');
    [, $otherMember] = MemberFixtures::member('PG-MEMBER-BOOKING-CANCEL-OTHER');
    $enrollment = MemberFixtures::enrollment($otherMember);

    $this->actingAs($user)->delete(route('member.bookings.destroy', $enrollment))
        ->assertForbidden();

    expect($enrollment->refresh()->status)->toBe('booked');
});

test('member booking page keeps schedule action forms protected with csrf tokens', function () {
    [$user, $member] = MemberFixtures::member('PG-MEMBER-BOOKING-CSRF', memberOverrides: ['gender' => 'female']);

    $package = MemberFixtures::package([
        'name' => 'Senam Booking CSRF',
        'type' => 'senam',
    ]);
    MemberFixtures::activeMembership($member, $package);
    MemberFixtures::schedule(['name' => 'Senam Booking Form', 'required_package_type' => 'senam']);

    $this->actingAs($user)->get(route('member.booking'))
        ->assertOk()
        ->assertSee('Senam Booking Form')
        ->assertSee('_token', false)
        ->assertSee('method="POST"', false)
        ->assertSee('Tanggal akan otomatis menyesuaikan jika tidak cocok.');
});
