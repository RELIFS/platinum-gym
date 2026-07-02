<?php

use App\Features\Bookings\Support\BookingTimePolicy;
use App\Models\ClassEnrollment;
use App\Models\ClassSchedule;
use App\Models\GymClass;
use App\Models\MemberPackageSession;
use App\Models\Trainer;
use Carbon\CarbonImmutable;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Support\Str;
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
        ->assertSee('memberBookingForm', false)
        ->assertDontSee('@js(', false)
        ->assertSee('name="session_date"', false)
        ->assertSee('name="session_date_display"', false)
        ->assertSee('x-modelable="isoValue"', false)
        ->assertSee('x-data="localDateInput', false)
        ->assertSee('data-local-date-picker="flatpickr"', false)
        ->assertSee('data-allowed-weekdays', false)
        ->assertSee('aria-label="Pilih tanggal"', false)
        ->assertSee('pointer-events-none absolute inset-y-0 right-0 h-full w-12 opacity-0', false)
        ->assertDontSee('emitModelValue()', false)
        ->assertDontSee('CustomEvent(\'input\'', false)
        ->assertDontSee('picker.showPicker();', false)
        ->assertDontSee('picker.focus();', false)
        ->assertSee('Booking minimal 1 hari sebelum jadwal.');
});

test('member can book included class with paid membership awaiting first check in', function () {
    [$user, $member] = MemberFixtures::member('PG-MEMBER-BOOKING-AWAITING', memberOverrides: ['gender' => 'female']);
    $package = MemberFixtures::package([
        'name' => 'Senam Awaiting Booking',
        'type' => 'senam',
    ]);
    MemberFixtures::activeMembership($member, $package, [
        'start_date' => null,
        'end_date' => null,
        'duration_days_snapshot' => 30,
        'activated_at' => now(),
    ]);
    $schedule = MemberFixtures::schedule([
        'name' => 'Senam Awaiting Included',
        'access_type' => 'included',
        'required_package_type' => 'senam',
    ]);
    $sessionDate = CarbonImmutable::today()->next(CarbonImmutable::MONDAY)->toDateString();

    $this->actingAs($user)
        ->post(route('member.booking.store', $schedule), [
            'session_date' => $sessionDate,
        ])
        ->assertRedirect(route('member.bookings'))
        ->assertSessionHas('status', 'Booking kelas berhasil tercatat.');

    expect(ClassEnrollment::query()
        ->where('member_id', $member->id)
        ->where('schedule_id', $schedule->id)
        ->whereDate('session_date', $sessionDate)
        ->where('status', 'booked')
        ->exists())->toBeTrue();
});

test('member cannot book class on the same day', function () {
    [$user, $member] = MemberFixtures::member('PG-MEMBER-BOOKING-H1', memberOverrides: ['gender' => 'female']);
    $package = MemberFixtures::package([
        'name' => 'Senam Booking H1',
        'type' => 'senam',
    ]);
    MemberFixtures::activeMembership($member, $package);
    $schedule = MemberFixtures::schedule([
        'name' => 'Senam Same Day Rejected',
        'access_type' => 'included',
        'required_package_type' => 'senam',
    ], [
        'day_of_week' => now()->dayOfWeekIso,
    ]);

    $this->actingAs($user)
        ->from(route('member.booking'))
        ->post(route('member.booking.store', $schedule), [
            'session_date' => now()->toDateString(),
        ])
        ->assertRedirect(route('member.booking'))
        ->assertSessionHasErrors(['session_date']);

    expect(ClassEnrollment::query()->where('member_id', $member->id)->where('schedule_id', $schedule->id)->exists())->toBeFalse();
});

test('member booking page keeps included class locked until matching membership exists', function () {
    [$user, $member] = MemberFixtures::member('PG-MEMBER-BOOKING-INCLUDED-LOCK', memberOverrides: ['gender' => 'female']);
    $schedule = MemberFixtures::schedule([
        'name' => 'Senam Included Lock',
        'access_type' => 'included',
        'required_package_type' => 'senam',
    ]);

    $lockedResponse = $this->actingAs($user)->get(route('member.booking'))->assertOk();
    $lockedContent = $lockedResponse->getContent();

    $lockedResponse
        ->assertSee('Senam Included Lock')
        ->assertSee('Booking Kelas')
        ->assertDontSee('Kelas ini membutuhkan membership aktif yang sesuai.');

    expect(preg_match('/<input\b[^>]*id="member-booking-session-date-'.$schedule->id.'"[^>]*>/s', $lockedContent, $lockedDateInput))->toBe(1);
    expect(preg_match('/\sdisabled(?:\s|>|=)/', $lockedDateInput[0]))->toBe(1);
    expect(preg_match('/<button\b(?=[^>]*type="submit")(?=[^>]*member-button-primary)(?=[^>]*disabled)(?=[^>]*aria-disabled="true")[^>]*>\s*Booking Kelas\s*<\/button>/s', $lockedContent))->toBe(1);

    $gymPackage = MemberFixtures::package([
        'name' => 'Gym Does Not Unlock Senam',
        'type' => 'gym',
    ]);
    MemberFixtures::activeMembership($member, $gymPackage);

    $wrongMembershipResponse = $this->actingAs($user)->get(route('member.booking'))->assertOk();
    $wrongMembershipContent = $wrongMembershipResponse->getContent();

    expect(preg_match('/<input\b[^>]*id="member-booking-session-date-'.$schedule->id.'"[^>]*>/s', $wrongMembershipContent, $wrongMembershipDateInput))->toBe(1);
    expect(preg_match('/\sdisabled(?:\s|>|=)/', $wrongMembershipDateInput[0]))->toBe(1);
    expect(preg_match('/<button\b(?=[^>]*type="submit")(?=[^>]*member-button-primary)(?=[^>]*disabled)(?=[^>]*aria-disabled="true")[^>]*>\s*Booking Kelas\s*<\/button>/s', $wrongMembershipContent))->toBe(1);

    $senamPackage = MemberFixtures::package([
        'name' => 'Senam Unlocks Included',
        'type' => 'senam',
    ]);
    MemberFixtures::activeMembership($member, $senamPackage);

    $unlockedResponse = $this->actingAs($user)->get(route('member.booking'))->assertOk();
    $unlockedContent = $unlockedResponse->getContent();

    expect(preg_match('/<input\b[^>]*id="member-booking-session-date-'.$schedule->id.'"[^>]*>/s', $unlockedContent, $unlockedDateInput))->toBe(1);
    expect(preg_match('/\sdisabled(?:\s|>|=)/', $unlockedDateInput[0]))->toBe(0);
    expect(preg_match('/<button\b(?=[^>]*type="submit")(?=[^>]*member-button-primary)(?![^>]*(?:disabled|aria-disabled="true"))[^>]*>\s*Booking Kelas\s*<\/button>/s', $unlockedContent))->toBe(1);
});

test('member booking page renders aerobic schedules as normal instructor cards', function () {
    [$user, $member] = MemberFixtures::member('PG-MEMBER-BOOKING-AEROBIC', memberOverrides: ['gender' => 'female']);
    $package = MemberFixtures::package([
        'name' => 'Senam Aerobic Group',
        'type' => 'senam',
    ]);
    MemberFixtures::activeMembership($member, $package);

    $gymClass = GymClass::create([
        'name' => 'Aerobic',
        'slug' => 'aerobic-member-group-'.Str::lower(Str::random(6)),
        'class_type' => 'aerobic',
        'access_type' => 'included',
        'required_package_type' => 'senam',
        'capacity' => 25,
        'is_active' => true,
    ]);
    $ola = Trainer::create(['name' => 'Coach Ola', 'specialization' => 'Aerobic', 'is_active' => true]);
    $irgo = Trainer::create(['name' => 'Coach Irgo', 'specialization' => 'Aerobic', 'is_active' => true]);

    ClassSchedule::create(['gym_class_id' => $gymClass->id, 'trainer_id' => $ola->id, 'day_of_week' => 1, 'start_time' => '17:15:00', 'end_time' => '18:15:00', 'capacity' => 25, 'is_active' => true]);
    ClassSchedule::create(['gym_class_id' => $gymClass->id, 'trainer_id' => $irgo->id, 'day_of_week' => 3, 'start_time' => '17:15:00', 'end_time' => '18:15:00', 'capacity' => 25, 'is_active' => true]);

    $this->actingAs($user)->get(route('member.booking'))
        ->assertOk()
        ->assertSee('Aerobic')
        ->assertSee('Instruktur')
        ->assertSee('Ola atau Irgo')
        ->assertSee('17:15')
        ->assertDontSee('2 pilihan jadwal')
        ->assertDontSee('>Ola<', false)
        ->assertDontSee('>Irgo<', false)
        ->assertDontSee('Ola/Irgo')
        ->assertDontSee('Coach Ola')
        ->assertDontSee('Coach Irgo');
});

test('member booking page keeps zin prefix for zumba instructor card', function () {
    [$user, $member] = MemberFixtures::member('PG-MEMBER-BOOKING-ZUMBA', memberOverrides: ['gender' => 'female']);
    $package = MemberFixtures::package([
        'name' => 'Senam Zumba Instructor',
        'type' => 'senam',
    ]);
    MemberFixtures::activeMembership($member, $package);

    $gymClass = GymClass::create([
        'name' => 'Zumba',
        'slug' => 'zumba-member-instructor-'.Str::lower(Str::random(6)),
        'class_type' => 'zumba',
        'access_type' => 'included',
        'required_package_type' => 'senam',
        'capacity' => 25,
        'is_active' => true,
    ]);
    $nila = Trainer::create(['name' => 'Zin Nila', 'specialization' => 'Zumba', 'is_active' => true]);

    ClassSchedule::create([
        'gym_class_id' => $gymClass->id,
        'trainer_id' => $nila->id,
        'day_of_week' => 2,
        'start_time' => '17:15:00',
        'end_time' => '18:15:00',
        'capacity' => 25,
        'is_active' => true,
    ]);

    $this->actingAs($user)->get(route('member.booking'))
        ->assertOk()
        ->assertSee('Zumba')
        ->assertSee('Instruktur')
        ->assertSee('>Zin Nila<', false)
        ->assertDontSee('>Nila<', false);
});

test('member booking page keeps poundfit card visible and locked until package session exists', function () {
    [$user, $member] = MemberFixtures::member('PG-MEMBER-BOOKING-POUNDFIT', memberOverrides: ['gender' => 'female']);
    $package = MemberFixtures::package([
        'name' => 'Poundfit Member Booking',
        'package_kind' => 'session',
        'type' => 'poundfit',
        'session_count' => 1,
        'duration_days' => null,
    ]);
    $sessionDate = BookingTimePolicy::earliestBookingDate();

    $gymClass = GymClass::create([
        'name' => 'Poundfit',
        'slug' => 'poundfit-member-pro-'.Str::lower(Str::random(6)),
        'class_type' => 'poundfit',
        'access_type' => 'session_based',
        'required_package_type' => 'poundfit',
        'capacity' => 20,
        'is_active' => true,
    ]);
    $trainer = Trainer::create(['name' => 'Rina', 'specialization' => 'Poundfit', 'is_active' => true]);

    $schedule = ClassSchedule::create([
        'gym_class_id' => $gymClass->id,
        'trainer_id' => $trainer->id,
        'day_of_week' => $sessionDate->dayOfWeekIso,
        'start_time' => '18:00:00',
        'end_time' => '19:00:00',
        'capacity' => 20,
        'is_active' => true,
    ]);

    $response = $this->actingAs($user)->get(route('member.booking'));
    $content = $response->getContent();

    $response
        ->assertOk()
        ->assertSee('Poundfit')
        ->assertSee('>Pro<', false)
        ->assertSee('>Rina<', false)
        ->assertSee('Booking Kelas')
        ->assertDontSee('Coach Rina')
        ->assertDontSee('2 pilihan jadwal')
        ->assertDontSee('Kelas ini membutuhkan membership aktif yang sesuai.');

    expect(preg_match('/<input\b[^>]*id="member-booking-session-date-'.$schedule->id.'"[^>]*>/s', $content, $dateInput))->toBe(1);
    expect(preg_match('/\sdisabled(?:\s|>|=)/', $dateInput[0]))->toBe(1);
    expect(preg_match('/<button\b(?=[^>]*type="submit")(?=[^>]*member-button-primary)(?=[^>]*disabled)(?=[^>]*aria-disabled="true")[^>]*>\s*Booking Kelas\s*<\/button>/s', $content))->toBe(1);

    $this->actingAs($user)
        ->from(route('member.booking'))
        ->post(route('member.booking.store', $schedule), ['session_date' => $sessionDate->toDateString()])
        ->assertRedirect(route('member.booking'))
        ->assertSessionHas('status', 'Kelas ini membutuhkan membership aktif yang sesuai.')
        ->assertSessionHas('status_kind', 'error');

    expect(ClassEnrollment::query()
        ->where('member_id', $member->id)
        ->where('schedule_id', $schedule->id)
        ->exists())->toBeFalse();

    MemberPackageSession::create([
        'member_id' => $member->id,
        'package_id' => $package->id,
        'code' => 'MPS-POUNDFIT-'.Str::upper(Str::random(6)),
        'total_sessions' => 1,
        'used_sessions' => 0,
        'remaining_sessions' => 1,
        'price' => $package->price,
        'started_at' => now()->toDateString(),
        'expired_at' => now()->addMonth()->toDateString(),
        'status' => 'active',
    ]);

    $unlockedResponse = $this->actingAs($user)->get(route('member.booking'))->assertOk();
    $unlockedContent = $unlockedResponse->getContent();

    expect(preg_match('/<input\b[^>]*id="member-booking-session-date-'.$schedule->id.'"[^>]*>/s', $unlockedContent, $unlockedDateInput))->toBe(1);
    expect(preg_match('/\sdisabled(?:\s|>|=)/', $unlockedDateInput[0]))->toBe(0);
    expect(preg_match('/<button\b(?=[^>]*type="submit")(?=[^>]*member-button-primary)(?![^>]*(?:disabled|aria-disabled="true"))[^>]*>\s*Booking Kelas\s*<\/button>/s', $unlockedContent))->toBe(1);

    $this->actingAs($user)
        ->post(route('member.booking.store', $schedule), ['session_date' => $sessionDate->toDateString()])
        ->assertRedirect(route('member.bookings'))
        ->assertSessionHas('status', 'Booking kelas berhasil tercatat.');
});

test('muaythai booking follows trainer selected on active package session', function () {
    [$user, $member] = MemberFixtures::member('PG-MEMBER-BOOKING-MUAY');
    $package = MemberFixtures::package([
        'name' => 'Muaythai Selected Coach',
        'package_kind' => 'session',
        'type' => 'muaythai',
        'session_count' => 4,
        'duration_days' => null,
    ]);
    $adi = Trainer::create(['name' => 'Coach Adi', 'specialization' => 'Muaythai', 'is_active' => true]);
    $arie = Trainer::create(['name' => 'Coach Arie', 'specialization' => 'Muaythai', 'is_active' => true]);
    $sessionDate = BookingTimePolicy::earliestBookingDate();

    MemberPackageSession::create([
        'member_id' => $member->id,
        'package_id' => $package->id,
        'trainer_id' => $adi->id,
        'code' => 'MPS-MUAY-'.Str::upper(Str::random(6)),
        'total_sessions' => 4,
        'used_sessions' => 0,
        'remaining_sessions' => 4,
        'price' => $package->price,
        'started_at' => now()->toDateString(),
        'expired_at' => now()->addMonth()->toDateString(),
        'status' => 'active',
    ]);

    $gymClass = GymClass::create([
        'name' => 'Muaythai',
        'slug' => 'muaythai-selected-coach-'.Str::lower(Str::random(6)),
        'class_type' => 'muaythai',
        'access_type' => 'session_based',
        'required_package_type' => 'muaythai',
        'capacity' => 6,
        'is_active' => true,
    ]);
    $adiSchedule = ClassSchedule::create(['gym_class_id' => $gymClass->id, 'trainer_id' => $adi->id, 'day_of_week' => $sessionDate->dayOfWeekIso, 'start_time' => '10:00:00', 'end_time' => '11:00:00', 'capacity' => 6, 'is_active' => true]);
    $arieSchedule = ClassSchedule::create(['gym_class_id' => $gymClass->id, 'trainer_id' => $arie->id, 'day_of_week' => $sessionDate->dayOfWeekIso, 'start_time' => '19:00:00', 'end_time' => '20:00:00', 'capacity' => 6, 'is_active' => true]);

    $this->actingAs($user)->get(route('member.booking'))
        ->assertOk()
        ->assertSee('Coach Adi')
        ->assertDontSee('Coach Arie');

    $this->actingAs($user)
        ->from(route('member.booking'))
        ->post(route('member.booking.store', $arieSchedule), ['session_date' => $sessionDate->toDateString()])
        ->assertRedirect(route('member.booking'))
        ->assertSessionHas('status_kind', 'error');

    $this->actingAs($user)
        ->post(route('member.booking.store', $adiSchedule), ['session_date' => $sessionDate->toDateString()])
        ->assertRedirect(route('member.bookings'))
        ->assertSessionHas('status', 'Booking kelas berhasil tercatat.');
});
