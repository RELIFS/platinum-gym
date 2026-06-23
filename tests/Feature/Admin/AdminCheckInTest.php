<?php

use App\Models\GymCheckIn;
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
