<?php

use Database\Seeders\RolePermissionSeeder;
use Tests\Feature\Member\Support\MemberPortalFixtures as MemberFixtures;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

test('member notifications page lists only current member notifications', function () {
    [$user] = MemberFixtures::member('PG-MEMBER-NOTIF-OWN');
    [$otherUser] = MemberFixtures::member('PG-MEMBER-NOTIF-OTHER');

    MemberFixtures::notification($user, ['title' => 'Notifikasi Milik Sendiri']);
    MemberFixtures::notification($otherUser, ['title' => 'Notifikasi Milik Orang Lain']);

    $this->actingAs($user)->get(route('member.notifications'))
        ->assertOk()
        ->assertSee('Notifikasi Milik Sendiri')
        ->assertDontSee('Notifikasi Milik Orang Lain');
});

test('member cannot mark another member notification as read', function () {
    [$user] = MemberFixtures::member('PG-MEMBER-NOTIF-ACTOR');
    [$otherUser] = MemberFixtures::member('PG-MEMBER-NOTIF-TARGET');
    $notification = MemberFixtures::notification($otherUser);

    $this->actingAs($user)->post(route('member.notifications.read', $notification))
        ->assertForbidden();

    expect($notification->refresh()->read_at)->toBeNull();
});

test('member read all only marks own unread notifications', function () {
    [$user] = MemberFixtures::member('PG-MEMBER-NOTIF-READALL');
    [$otherUser] = MemberFixtures::member('PG-MEMBER-NOTIF-READALL-OTHER');

    $ownNotification = MemberFixtures::notification($user);
    $otherNotification = MemberFixtures::notification($otherUser);

    $this->actingAs($user)->from(route('member.notifications'))->post(route('member.notifications.read-all'))
        ->assertRedirect(route('member.notifications'));

    expect($ownNotification->refresh()->read_at)->not->toBeNull()
        ->and($otherNotification->refresh()->read_at)->toBeNull();
});
