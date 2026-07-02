<?php

use App\Models\ClassAttendance;
use App\Models\ClassEnrollment;
use App\Models\GymCheckIn;
use App\Models\Member;
use App\Models\MemberPackageSession;
use App\Models\MemberPackageSessionUsage;
use App\Models\Package as ServicePackage;
use App\Models\QrToken;
use App\Models\Trainer;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Support\Str;
use Tests\Feature\Admin\Support\AdminPortalFixtures as AdminFixtures;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

function makeAdminCheckInQr(Member $member): QrToken
{
    return QrToken::create([
        'tokenable_type' => Member::class,
        'tokenable_id' => $member->id,
        'token' => Str::random(64),
        'purpose' => 'member',
        'is_revoked' => false,
    ]);
}

function makeAdminCheckInSession(Member $member, string $type = 'muaythai', ?Trainer $trainer = null, array $overrides = []): MemberPackageSession
{
    $package = ServicePackage::create(array_merge([
        'name' => Str::headline($type).' Session QA',
        'slug' => $type.'-session-qa-'.Str::lower(Str::random(6)),
        'package_kind' => 'session',
        'type' => $type,
        'price' => 400000,
        'session_count' => 4,
        'is_active' => true,
    ], $overrides['package'] ?? []));

    return MemberPackageSession::create(array_merge([
        'member_id' => $member->id,
        'package_id' => $package->id,
        'trainer_id' => $trainer?->id,
        'code' => 'MPS-CHECKIN-'.Str::upper(Str::random(8)),
        'total_sessions' => 4,
        'used_sessions' => 0,
        'remaining_sessions' => 4,
        'price' => 400000,
        'started_at' => now()->subDay()->toDateString(),
        'expired_at' => now()->addMonth()->toDateString(),
        'status' => 'active',
    ], $overrides['session'] ?? []));
}

function makeAdminCheckInClassBooking(Member $member, string $status = 'confirmed', string $type = 'muaythai', ?Trainer $trainer = null, ?string $date = null): ClassEnrollment
{
    $date ??= now()->toDateString();

    [, $schedule] = AdminFixtures::schedule($date, [
        'name' => Str::headline($type).' Check In QA',
        'slug' => $type.'-check-in-qa-'.Str::lower(Str::random(6)),
        'class_type' => $type,
        'access_type' => 'session_based',
        'required_package_type' => $type,
        'capacity' => 12,
    ], [
        'trainer_id' => $trainer?->id,
        'capacity' => 12,
    ]);

    return AdminFixtures::enrollment($member, $schedule, [
        'session_date' => $date,
        'status' => $status,
    ]);
}

test('admin cannot use class package session without confirmed booking today', function () {
    $admin = AdminFixtures::admin();
    [, $member] = AdminFixtures::member('PG-CHECKIN-NO-BOOKING');
    $packageSession = makeAdminCheckInSession($member, 'muaythai');
    $qrToken = makeAdminCheckInQr($member);

    $this->actingAs($admin)->post(route('admin.check-in.preview'), [
        'token' => $qrToken->token,
    ])->assertRedirect()->assertSessionHas('check_in_preview');

    $preview = session('check_in_preview');

    expect($preview['sessions'])->toBe([])
        ->and($preview['session_notice'])->toBe('Tidak ada booking kelas confirmed hari ini untuk paket sesi ini.');

    $this->actingAs($admin)->get(route('admin.check-in'))
        ->assertOk()
        ->assertSee('Tidak ada booking kelas confirmed hari ini untuk paket sesi ini.')
        ->assertSee('value="use_package_session" class="admin-button-secondary w-full"', false)
        ->assertSee('disabled', false);

    $this->actingAs($admin)->post(route('admin.check-in.confirm'), [
        'preview_key' => $preview['preview_key'],
        'action' => 'use_package_session',
        'member_package_session_id' => $packageSession->id,
    ])->assertRedirect()->assertSessionHas('status_kind', 'error');

    expect($packageSession->refresh())
        ->used_sessions->toBe(0)
        ->remaining_sessions->toBe(4)
        ->and(MemberPackageSessionUsage::query()->where('member_id', $member->id)->count())->toBe(0);
});

test('admin can use class package session only for confirmed booking today', function () {
    $admin = AdminFixtures::admin();
    [, $member] = AdminFixtures::member('PG-CHECKIN-CONFIRMED');
    $trainer = AdminFixtures::trainer(['name' => 'Coach Confirmed', 'specialization' => 'Muaythai']);
    $packageSession = makeAdminCheckInSession($member, 'muaythai', $trainer);
    $enrollment = makeAdminCheckInClassBooking($member, 'confirmed', 'muaythai', $trainer);
    $qrToken = makeAdminCheckInQr($member);

    $this->actingAs($admin)->post(route('admin.check-in.preview'), [
        'token' => $qrToken->token,
    ])->assertRedirect()->assertSessionHas('check_in_preview');

    $preview = session('check_in_preview');

    expect($preview['sessions'])->toHaveCount(1)
        ->and($preview['sessions'][0]['id'])->toBe($packageSession->id)
        ->and($preview['sessions'][0]['class_enrollment_id'])->toBe($enrollment->id)
        ->and($preview['sessions'][0]['class_name'])->toContain('Muaythai')
        ->and($preview['sessions'][0]['trainer'])->toBe('Coach Confirmed');

    $this->actingAs($admin)->post(route('admin.check-in.confirm'), [
        'preview_key' => $preview['preview_key'],
        'action' => 'use_package_session',
        'member_package_session_id' => $packageSession->id,
        'class_enrollment_id' => $enrollment->id,
    ])->assertRedirect()->assertSessionHas('status');

    $usage = MemberPackageSessionUsage::query()->where('member_package_session_id', $packageSession->id)->firstOrFail();

    expect($packageSession->refresh())
        ->used_sessions->toBe(1)
        ->remaining_sessions->toBe(3)
        ->and($usage->class_enrollment_id)->toBe($enrollment->id)
        ->and($usage->gym_check_in_id)->toBeNull()
        ->and($enrollment->refresh()->status)->toBe('attended')
        ->and(ClassAttendance::query()->where('enrollment_id', $enrollment->id)->exists())->toBeTrue()
        ->and(GymCheckIn::query()->where('member_id', $member->id)->exists())->toBeFalse();
});

test('admin cannot use class package session for unconfirmed past or mismatched booking', function (string $status, int $dayOffset, ?string $sessionType, ?string $packageType) {
    $admin = AdminFixtures::admin();
    [, $member] = AdminFixtures::member('PG-CHECKIN-GUARD-'.Str::upper(Str::random(4)));
    $trainer = AdminFixtures::trainer(['specialization' => 'Muaythai']);
    $packageSession = makeAdminCheckInSession($member, $packageType ?? 'muaythai', $trainer);
    $enrollment = makeAdminCheckInClassBooking(
        $member,
        $status,
        $sessionType ?? 'muaythai',
        $trainer,
        now()->addDays($dayOffset)->toDateString(),
    );
    $qrToken = makeAdminCheckInQr($member);

    $this->actingAs($admin)->post(route('admin.check-in.preview'), [
        'token' => $qrToken->token,
    ])->assertRedirect()->assertSessionHas('check_in_preview');

    $preview = session('check_in_preview');

    expect($preview['sessions'])->toBe([]);

    $this->actingAs($admin)->post(route('admin.check-in.confirm'), [
        'preview_key' => $preview['preview_key'],
        'action' => 'use_package_session',
        'member_package_session_id' => $packageSession->id,
        'class_enrollment_id' => $enrollment->id,
    ])->assertRedirect()->assertSessionHas('status_kind', 'error');

    expect($packageSession->refresh())
        ->used_sessions->toBe(0)
        ->remaining_sessions->toBe(4)
        ->and($enrollment->refresh()->status)->toBe($status);
})->with([
    'booked today' => ['booked', 0, 'muaythai', 'muaythai'],
    'confirmed tomorrow' => ['confirmed', 1, 'muaythai', 'muaythai'],
    'confirmed today different type' => ['confirmed', 0, 'poundfit', 'muaythai'],
]);

test('admin cannot use trainer-bound muaythai session for another coach schedule', function () {
    $admin = AdminFixtures::admin();
    [, $member] = AdminFixtures::member('PG-CHECKIN-TRAINER-MISMATCH');
    $packageTrainer = AdminFixtures::trainer(['name' => 'Coach Package', 'specialization' => 'Muaythai']);
    $scheduleTrainer = AdminFixtures::trainer(['name' => 'Coach Schedule', 'specialization' => 'Muaythai']);
    $packageSession = makeAdminCheckInSession($member, 'muaythai', $packageTrainer);
    $enrollment = makeAdminCheckInClassBooking($member, 'confirmed', 'muaythai', $scheduleTrainer);
    $qrToken = makeAdminCheckInQr($member);

    $this->actingAs($admin)->post(route('admin.check-in.preview'), [
        'token' => $qrToken->token,
    ])->assertRedirect()->assertSessionHas('check_in_preview');

    $preview = session('check_in_preview');

    expect($preview['sessions'])->toBe([]);

    $this->actingAs($admin)->post(route('admin.check-in.confirm'), [
        'preview_key' => $preview['preview_key'],
        'action' => 'use_package_session',
        'member_package_session_id' => $packageSession->id,
        'class_enrollment_id' => $enrollment->id,
    ])->assertRedirect()->assertSessionHas('status_kind', 'error');

    expect($packageSession->refresh())->remaining_sessions->toBe(4);
});

test('duplicate class package session usage for same booking does not decrement again', function () {
    $admin = AdminFixtures::admin();
    [, $member] = AdminFixtures::member('PG-CHECKIN-DUPLICATE');
    $trainer = AdminFixtures::trainer(['specialization' => 'Muaythai']);
    $packageSession = makeAdminCheckInSession($member, 'muaythai', $trainer);
    $enrollment = makeAdminCheckInClassBooking($member, 'confirmed', 'muaythai', $trainer);
    $qrToken = makeAdminCheckInQr($member);

    $this->actingAs($admin)->post(route('admin.check-in.preview'), ['token' => $qrToken->token])
        ->assertRedirect()
        ->assertSessionHas('check_in_preview');

    $preview = session('check_in_preview');
    $payload = [
        'preview_key' => $preview['preview_key'],
        'action' => 'use_package_session',
        'member_package_session_id' => $packageSession->id,
        'class_enrollment_id' => $enrollment->id,
    ];

    $this->actingAs($admin)->post(route('admin.check-in.confirm'), $payload)->assertRedirect();
    $this->actingAs($admin)->post(route('admin.check-in.preview'), ['token' => $qrToken->token])
        ->assertRedirect()
        ->assertSessionHas('check_in_preview');

    $secondPreview = session('check_in_preview');

    $this->actingAs($admin)->post(route('admin.check-in.confirm'), array_merge($payload, [
        'preview_key' => $secondPreview['preview_key'],
    ]))->assertRedirect()->assertSessionHas('status_kind', 'error');

    expect($packageSession->refresh())
        ->used_sessions->toBe(1)
        ->remaining_sessions->toBe(3)
        ->and(MemberPackageSessionUsage::query()->where('class_enrollment_id', $enrollment->id)->count())->toBe(1);
});

test('personal trainer session remains usable with active membership without class booking', function () {
    $admin = AdminFixtures::admin();
    [, $member] = AdminFixtures::member('PG-CHECKIN-PT');
    AdminFixtures::membership($member);
    $packageSession = makeAdminCheckInSession($member, 'personal_trainer');
    $qrToken = makeAdminCheckInQr($member);

    $this->actingAs($admin)->post(route('admin.check-in.preview'), [
        'token' => $qrToken->token,
    ])->assertRedirect()->assertSessionHas('check_in_preview');

    $preview = session('check_in_preview');

    expect($preview['sessions'])->toHaveCount(1)
        ->and($preview['sessions'][0]['id'])->toBe($packageSession->id)
        ->and($preview['sessions'][0]['class_enrollment_id'])->toBeNull();

    $this->actingAs($admin)->post(route('admin.check-in.confirm'), [
        'preview_key' => $preview['preview_key'],
        'action' => 'use_package_session',
        'member_package_session_id' => $packageSession->id,
    ])->assertRedirect()->assertSessionHas('status');

    $usage = MemberPackageSessionUsage::query()->where('member_package_session_id', $packageSession->id)->firstOrFail();

    expect($packageSession->refresh())
        ->used_sessions->toBe(1)
        ->remaining_sessions->toBe(3)
        ->and($usage->class_enrollment_id)->toBeNull();
});
