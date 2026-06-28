<?php

use App\Models\GymCheckIn;
use App\Models\MemberPackageSession;
use App\Models\MemberPackageSessionUsage;
use Database\Seeders\RolePermissionSeeder;
use Tests\Feature\Admin\Support\AdminPortalFixtures as AdminFixture;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

test('admin qr preview validates member without creating check in record', function () {
    $admin = AdminFixture::admin();
    [, $member] = AdminFixture::member('PG-ADM-QR');
    AdminFixture::membership($member);
    $qrToken = AdminFixture::qrToken($member);

    $this->actingAs($admin)
        ->post(route('admin.check-in.preview'), ['token' => $qrToken->token])
        ->assertRedirect()
        ->assertSessionHas('status', 'QR valid. Cek data member lalu konfirmasi tindakan.')
        ->assertSessionHas('check_in_preview');

    expect(GymCheckIn::query()->where('member_id', $member->id)->exists())->toBeFalse();
});

test('admin qr preview rejects revoked token and member without active membership', function () {
    $admin = AdminFixture::admin();
    [, $member] = AdminFixture::member('PG-ADM-QR-REVOKED');
    AdminFixture::membership($member);
    $revoked = AdminFixture::qrToken($member, ['is_revoked' => true]);

    $this->actingAs($admin)
        ->post(route('admin.check-in.preview'), ['token' => $revoked->token])
        ->assertRedirect()
        ->assertSessionHas('status_kind', 'error')
        ->assertSessionHas('status', 'QR member tidak valid.');

    [, $inactiveMember] = AdminFixture::member('PG-ADM-QR-INACTIVE');
    $inactiveToken = AdminFixture::qrToken($inactiveMember);

    $this->actingAs($admin)
        ->post(route('admin.check-in.preview'), ['token' => $inactiveToken->token])
        ->assertRedirect()
        ->assertSessionHas('status_kind', 'error')
        ->assertSessionHas('status', 'Membership aktif tidak ditemukan.');
});

test('admin qr confirm requires valid preview session key', function () {
    $admin = AdminFixture::admin();

    $this->actingAs($admin)
        ->post(route('admin.check-in.confirm'), [
            'preview_key' => 'missing-preview',
            'action' => 'check_in_membership',
        ])
        ->assertRedirect()
        ->assertSessionHas('status_kind', 'error')
        ->assertSessionHas('status', 'Preview check-in sudah tidak berlaku. Scan ulang QR member.');
});

test('admin qr confirm records membership check in once', function () {
    $admin = AdminFixture::admin();
    [, $member] = AdminFixture::member('PG-ADM-QR-CONFIRM');
    AdminFixture::membership($member);
    $qrToken = AdminFixture::qrToken($member);

    $this->actingAs($admin)
        ->withSession(['admin_check_in_preview_tokens.preview-ok' => $qrToken->token])
        ->post(route('admin.check-in.confirm'), [
            'preview_key' => 'preview-ok',
            'action' => 'check_in_membership',
        ])
        ->assertRedirect()
        ->assertSessionHas('status');

    expect(GymCheckIn::query()->where('member_id', $member->id)->count())->toBe(1);

    $this->actingAs($admin)
        ->withSession(['admin_check_in_preview_tokens.preview-again' => $qrToken->token])
        ->post(route('admin.check-in.confirm'), [
            'preview_key' => 'preview-again',
            'action' => 'check_in_membership',
        ])
        ->assertRedirect()
        ->assertSessionHas('status_kind', 'error')
        ->assertSessionHas('status', 'Member sudah check-in hari ini.');
});

test('admin check in starts paid membership duration on first successful check in only', function () {
    $admin = AdminFixture::admin();
    [, $member] = AdminFixture::member('PG-ADM-QR-DEFERRED');
    $membership = AdminFixture::membership($member, overrides: [
        'start_date' => null,
        'end_date' => null,
        'duration_days_snapshot' => 120,
        'activated_at' => now(),
        'status' => 'active',
    ]);
    $qrToken = AdminFixture::qrToken($member);

    $this->actingAs($admin)
        ->post(route('admin.check-in.preview'), ['token' => $qrToken->token])
        ->assertRedirect()
        ->assertSessionHas('check_in_preview.membership.end_date', 'Mulai saat check-in pertama');

    $this->actingAs($admin)
        ->withSession(['admin_check_in_preview_tokens.preview-deferred' => $qrToken->token])
        ->post(route('admin.check-in.confirm'), [
            'preview_key' => 'preview-deferred',
            'action' => 'check_in_membership',
        ])
        ->assertRedirect()
        ->assertSessionHas('status');

    $startedAt = now()->toDateString();
    $endsAt = now()->addDays(119)->toDateString();

    expect($membership->refresh())
        ->start_date->toDateString()->toBe($startedAt)
        ->end_date->toDateString()->toBe($endsAt);

    $this->travel(1)->day();

    $this->actingAs($admin)
        ->withSession(['admin_check_in_preview_tokens.preview-next-day' => $qrToken->token])
        ->post(route('admin.check-in.confirm'), [
            'preview_key' => 'preview-next-day',
            'action' => 'check_in_membership',
        ])
        ->assertRedirect()
        ->assertSessionHas('status');

    expect($membership->refresh())
        ->start_date->toDateString()->toBe($startedAt)
        ->end_date->toDateString()->toBe($endsAt)
        ->and(GymCheckIn::query()->where('member_id', $member->id)->count())->toBe(2);

    $this->travelBack();
});

test('admin check in plus session records check in only when selected package session was already used today', function () {
    $admin = AdminFixture::admin();
    [, $member] = AdminFixture::member('PG-ADM-QR-DUPLICATE-SESSION');
    AdminFixture::membership($member);
    $sessionPackage = AdminFixture::package([
        'name' => 'Duplicate Session Guard',
        'slug' => 'duplicate-session-guard',
        'package_kind' => 'session',
        'type' => 'muaythai',
        'session_count' => 4,
    ]);
    $packageSession = MemberPackageSession::create([
        'member_id' => $member->id,
        'package_id' => $sessionPackage->id,
        'code' => 'MPS-DUPLICATE-GUARD',
        'total_sessions' => 4,
        'used_sessions' => 1,
        'remaining_sessions' => 3,
        'price' => 400000,
        'started_at' => now()->subDay()->toDateString(),
        'expired_at' => now()->addMonth()->toDateString(),
        'status' => 'active',
    ]);
    $usage = MemberPackageSessionUsage::create([
        'member_package_session_id' => $packageSession->id,
        'member_id' => $member->id,
        'gym_check_in_id' => null,
        'usage_date' => now()->toDateString(),
        'used_at' => now()->subMinute(),
        'method' => 'admin_qr',
        'recorded_by' => $admin->id,
        'request_key' => 'duplicate-session-used-first',
    ]);
    $qrToken = AdminFixture::qrToken($member);

    $this->actingAs($admin)
        ->withSession(['admin_check_in_preview_tokens.preview-duplicate-session' => $qrToken->token])
        ->post(route('admin.check-in.confirm'), [
            'preview_key' => 'preview-duplicate-session',
            'action' => 'check_in_and_use_session',
            'member_package_session_id' => $packageSession->id,
        ])
        ->assertRedirect()
        ->assertSessionHas('status');

    expect($packageSession->refresh())
        ->used_sessions->toBe(1)
        ->remaining_sessions->toBe(3)
        ->and(MemberPackageSessionUsage::query()->where('member_package_session_id', $packageSession->id)->count())->toBe(1)
        ->and($usage->refresh()->gym_check_in_id)->toBeNull()
        ->and(GymCheckIn::query()->where('member_id', $member->id)->count())->toBe(1);

    $this->actingAs($admin)
        ->get(route('admin.check-in'))
        ->assertOk()
        ->assertSee('Check-in')
        ->assertSee('Sesi')
        ->assertDontSee('Check-in + Sesi');
});

test('admin use package session is idempotent when selected session was already used today', function () {
    $admin = AdminFixture::admin();
    [, $member] = AdminFixture::member('PG-ADM-QR-IDEMPOTENT-SESSION');
    AdminFixture::membership($member);
    $sessionPackage = AdminFixture::package([
        'name' => 'Idempotent Session Guard',
        'slug' => 'idempotent-session-guard',
        'package_kind' => 'session',
        'type' => 'pt',
        'session_count' => 4,
    ]);
    $packageSession = MemberPackageSession::create([
        'member_id' => $member->id,
        'package_id' => $sessionPackage->id,
        'code' => 'MPS-IDEMPOTENT-GUARD',
        'total_sessions' => 4,
        'used_sessions' => 1,
        'remaining_sessions' => 3,
        'price' => 400000,
        'started_at' => now()->subDay()->toDateString(),
        'expired_at' => now()->addMonth()->toDateString(),
        'status' => 'active',
    ]);
    MemberPackageSessionUsage::create([
        'member_package_session_id' => $packageSession->id,
        'member_id' => $member->id,
        'gym_check_in_id' => null,
        'usage_date' => now()->toDateString(),
        'used_at' => now()->subMinute(),
        'method' => 'admin_qr',
        'recorded_by' => $admin->id,
        'request_key' => 'idempotent-session-used-first',
    ]);
    $qrToken = AdminFixture::qrToken($member);

    $this->actingAs($admin)
        ->withSession(['admin_check_in_preview_tokens.preview-idempotent-session' => $qrToken->token])
        ->post(route('admin.check-in.confirm'), [
            'preview_key' => 'preview-idempotent-session',
            'action' => 'use_package_session',
            'member_package_session_id' => $packageSession->id,
        ])
        ->assertRedirect()
        ->assertSessionHas('status');

    expect($packageSession->refresh())
        ->used_sessions->toBe(1)
        ->remaining_sessions->toBe(3)
        ->and(MemberPackageSessionUsage::query()->where('member_package_session_id', $packageSession->id)->count())->toBe(1)
        ->and(GymCheckIn::query()->where('member_id', $member->id)->count())->toBe(0);
});
