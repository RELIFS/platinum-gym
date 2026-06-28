<?php

use App\Models\ClassEnrollment;
use App\Models\ClassSchedule;
use App\Models\GymClass;
use App\Models\Membership;
use App\Notifications\Bookings\BookingCancelledNotification;
use App\Notifications\Bookings\BookingConfirmedNotification;
use App\Notifications\Bookings\BookingCreatedNotification;
use Carbon\CarbonImmutable;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Tests\Feature\Admin\Support\AdminPortalFixtures as AdminFixture;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

test('admin booking form renders date helper and field level validation errors', function () {
    $admin = AdminFixture::admin();
    AdminFixture::schedule();

    $this->actingAs($admin)
        ->get('/admin/booking')
        ->assertOk()
        ->assertSee('Tambah booking kelas')
        ->assertSee('grid items-start gap-4 overflow-visible', false)
        ->assertDontSee('md:items-end', false)
        ->assertSee('Cari nama atau kode member')
        ->assertSee('role="combobox"', false)
        ->assertSee('x-ref="memberSearch"', false)
        ->assertSee('x-bind:aria-activedescendant="activeOptionId"', false)
        ->assertSee('class="admin-form-input pr-12"', false)
        ->assertSee('aria-label="Bersihkan pilihan member"', false)
        ->assertSee('absolute left-0 top-full z-50', false)
        ->assertSee('name="member_id"', false)
        ->assertSee('name="session_date"', false)
        ->assertSee('name="session_date_display"', false)
        ->assertSee('md:col-span-2 2xl:col-span-1 2xl:mt-8', false)
        ->assertSee('relative block min-w-0', false)
        ->assertSee('x-modelable="isoValue"', false)
        ->assertSee('aria-label="Pilih tanggal"', false)
        ->assertSee('pointer-events-none absolute inset-y-0 right-0 h-full w-12 opacity-0', false)
        ->assertSee('emitModelValue()', false)
        ->assertSee('CustomEvent(\'input\'', false)
        ->assertSee('picker.showPicker();', false)
        ->assertSee('catch (error)', false)
        ->assertSee('picker.focus();', false)
        ->assertSee('Riwayat Booking Kelas')
        ->assertSee('Tanggal mengikuti hari jadwal kelas dan minimal 1 hari sebelum jadwal.')
        ->assertSee('Jadwal mengikuti paket aktif member.')
        ->assertSee('eligibleSchedules', false)
        ->assertSee('schedulePlaceholder', false)
        ->assertSee('x-text="schedulePlaceholder"', false)
        ->assertSee('admin-member-selected', false)
        ->assertSee('x-bind:data-day-of-week', false)
        ->assertSee('data-day-of-week', false)
        ->assertSee('adminBookingForm', false)
        ->assertSee('adminMemberCombobox', false);

    $this->actingAs($admin)
        ->from('/admin/booking')
        ->post(route('admin.booking.store'), [
            'member_id' => '',
            'schedule_id' => '',
            'session_date' => now()->subDay()->toDateString(),
        ])
        ->assertRedirect('/admin/booking')
        ->assertSessionHasErrors(['member_id', 'schedule_id', 'session_date']);
});

test('admin booking schedule dropdown and backend guard follow member purchased access', function () {
    Notification::fake();
    $admin = AdminFixture::admin();
    [, $member] = AdminFixture::member('PG-ADM-ELIGIBLE-SENAM');
    $sessionDate = now()->addDay();
    $sessionDateString = $sessionDate->toDateString();
    $scheduleIso = $sessionDate->dayOfWeekIso;
    $senamPackage = AdminFixture::package([
        'name' => 'Senam Eligibility QA',
        'slug' => 'senam-eligibility-qa-'.Str::lower(Str::random(6)),
        'package_kind' => 'membership',
        'type' => 'senam',
    ]);

    Membership::create([
        'member_id' => $member->id,
        'package_id' => $senamPackage->id,
        'code' => 'MBR-ADM-ELIGIBLE',
        'start_date' => null,
        'end_date' => null,
        'price' => $senamPackage->price,
        'duration_days_snapshot' => 30,
        'status' => 'active',
        'activated_at' => now(),
    ]);

    $senamClass = GymClass::create([
        'name' => 'Senam Eligible QA',
        'slug' => 'senam-eligible-qa-'.Str::lower(Str::random(6)),
        'class_type' => 'zumba',
        'access_type' => 'included',
        'required_package_type' => 'senam',
        'capacity' => 20,
        'is_active' => true,
    ]);
    $gymClass = GymClass::create([
        'name' => 'Gym Not Eligible QA',
        'slug' => 'gym-not-eligible-qa-'.Str::lower(Str::random(6)),
        'class_type' => 'gym',
        'access_type' => 'included',
        'required_package_type' => 'gym',
        'capacity' => 20,
        'is_active' => true,
    ]);
    $senamSchedule = ClassSchedule::create([
        'gym_class_id' => $senamClass->id,
        'day_of_week' => $scheduleIso,
        'start_time' => '08:00:00',
        'end_time' => '09:00:00',
        'capacity' => 20,
        'is_active' => true,
    ]);
    $gymSchedule = ClassSchedule::create([
        'gym_class_id' => $gymClass->id,
        'day_of_week' => $scheduleIso,
        'start_time' => '10:00:00',
        'end_time' => '11:00:00',
        'capacity' => 20,
        'is_active' => true,
    ]);

    $this->actingAs($admin)
        ->get('/admin/booking')
        ->assertOk()
        ->assertSee((string) $senamSchedule->id)
        ->assertSee((string) $gymSchedule->id)
        ->assertSee('Jadwal mengikuti paket aktif member.');

    $this->actingAs($admin)
        ->from('/admin/booking')
        ->post(route('admin.booking.store'), [
            'member_id' => $member->id,
            'schedule_id' => $gymSchedule->id,
            'session_date' => $sessionDateString,
        ])
        ->assertRedirect('/admin/booking')
        ->assertSessionHasErrors(['schedule_id' => 'Jadwal kelas tidak sesuai dengan paket aktif member.']);

    $this->actingAs($admin)
        ->from('/admin/booking')
        ->post(route('admin.booking.store'), [
            'member_id' => $member->id,
            'schedule_id' => $senamSchedule->id,
            'session_date' => $sessionDateString,
        ])
        ->assertRedirect()
        ->assertSessionHas('status', 'Booking kelas berhasil dibuat oleh admin.');

    expect(ClassEnrollment::query()
        ->where('member_id', $member->id)
        ->where('schedule_id', $senamSchedule->id)
        ->whereDate('session_date', $sessionDateString)
        ->exists())->toBeTrue();

    Notification::assertSentTo($member->user, BookingCreatedNotification::class, function (BookingCreatedNotification $notification) use ($member, $senamClass): bool {
        $rendered = $notification->toMail($member->user)->render();

        return str_contains($rendered, 'Booking kelas')
            && str_contains($rendered, $senamClass->name)
            && str_contains($rendered, 'Lihat Riwayat Booking');
    });
});

test('admin can confirm booked booking but cannot bypass pending payment or attended bookings', function () {
    Notification::fake();
    $admin = AdminFixture::admin();
    [, $member] = AdminFixture::member();
    $booked = AdminFixture::enrollment($member, overrides: ['status' => 'booked']);
    $pendingPayment = AdminFixture::enrollment($member, overrides: [
        'session_date' => now()->addDay()->toDateString(),
        'status' => 'pending_payment',
    ]);
    $attended = AdminFixture::enrollment($member, overrides: [
        'session_date' => now()->addDays(2)->toDateString(),
        'status' => 'attended',
    ]);

    $this->actingAs($admin)
        ->post(route('admin.booking.confirm', $booked))
        ->assertRedirect()
        ->assertSessionHas('status', 'Booking kelas berhasil dikonfirmasi.');

    expect($booked->refresh()->status)->toBe('confirmed');
    Notification::assertSentTo($member->user, BookingConfirmedNotification::class);

    $this->actingAs($admin)
        ->post(route('admin.booking.confirm', $pendingPayment))
        ->assertRedirect()
        ->assertSessionHas('status_kind', 'error')
        ->assertSessionHas('status', 'Booking berbayar masih menunggu pembayaran. Konfirmasi dilakukan otomatis setelah pembayaran lunas.');

    $this->actingAs($admin)
        ->post(route('admin.booking.confirm', $attended))
        ->assertRedirect()
        ->assertSessionHas('status_kind', 'error');

    expect($pendingPayment->refresh()->status)->toBe('pending_payment')
        ->and($attended->refresh()->status)->toBe('attended');
});

test('admin cancel uses safe booking action and rejects attended or past booking', function () {
    Notification::fake();
    $admin = AdminFixture::admin();
    [, $member] = AdminFixture::member();
    $cancelable = AdminFixture::enrollment($member, overrides: [
        'session_date' => now()->addDay()->toDateString(),
        'status' => 'booked',
    ]);
    $attended = AdminFixture::enrollment($member, overrides: [
        'session_date' => now()->addDays(2)->toDateString(),
        'status' => 'confirmed',
    ]);
    AdminFixture::attendance($attended, $admin);
    $past = AdminFixture::enrollment($member, overrides: [
        'session_date' => now()->subDay()->toDateString(),
        'status' => 'booked',
    ]);

    $this->actingAs($admin)
        ->post(route('admin.booking.cancel', $cancelable))
        ->assertRedirect()
        ->assertSessionHas('status', 'Booking kelas berhasil dibatalkan.');

    $this->actingAs($admin)
        ->post(route('admin.booking.cancel', $attended))
        ->assertRedirect()
        ->assertSessionHas('status_kind', 'error');

    $this->actingAs($admin)
        ->post(route('admin.booking.cancel', $past))
        ->assertRedirect()
        ->assertSessionHas('status_kind', 'error');

    expect($cancelable->refresh()->status)->toBe('cancelled')
        ->and($cancelable->cancel_reason)->toBe('Dibatalkan oleh admin.')
        ->and($attended->refresh()->status)->toBe('confirmed')
        ->and($past->refresh()->status)->toBe('booked');
    Notification::assertSentTo($member->user, BookingCancelledNotification::class);
});

test('admin cancel rejects booking inside three hour cutoff', function () {
    Notification::fake();
    $admin = AdminFixture::admin();
    [, $member] = AdminFixture::member('PG-ADM-CANCEL-CUTOFF');

    $this->travelTo(CarbonImmutable::parse('2026-01-05 15:00:00'));

    $enrollment = AdminFixture::enrollment($member, overrides: [
        'session_date' => '2026-01-05',
        'status' => 'booked',
    ]);

    $this->actingAs($admin)
        ->post(route('admin.booking.cancel', $enrollment))
        ->assertRedirect()
        ->assertSessionHas('status_kind', 'error')
        ->assertSessionHas('status', 'Booking kelas hanya bisa dibatalkan paling lambat 3 jam sebelum kelas dimulai.');

    expect($enrollment->refresh()->status)->toBe('booked');

    $this->travelBack();
});

test('admin booking page only exposes confirmation action for actionable bookings', function () {
    $admin = AdminFixture::admin();
    [, $member] = AdminFixture::member('PG-ADM-BOOKING-ACTION');

    AdminFixture::enrollment($member, overrides: ['status' => 'booked']);
    AdminFixture::enrollment($member, overrides: [
        'status' => 'pending_payment',
    ]);
    AdminFixture::enrollment($member, overrides: [
        'status' => 'confirmed',
    ]);

    $response = $this->actingAs($admin)->get('/admin/booking')->assertOk();

    $response->assertSee('Konfirmasi', false)
        ->assertSee('Menunggu pembayaran lunas sebelum bisa dikonfirmasi.')
        ->assertSee('Booking sudah siap untuk proses check-in.');
});

test('admin booking history includes non today booking rows', function () {
    $admin = AdminFixture::admin();
    [, $member] = AdminFixture::member('PG-ADM-HISTORY');
    AdminFixture::enrollment($member, overrides: [
        'session_date' => now()->addDays(10)->toDateString(),
        'status' => 'confirmed',
    ]);

    $this->actingAs($admin)
        ->get('/admin/booking')
        ->assertOk()
        ->assertSee('Riwayat Booking Kelas')
        ->assertSee('PG-ADM-HISTORY');
});
