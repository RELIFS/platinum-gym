<?php

use Database\Seeders\RolePermissionSeeder;
use Illuminate\Support\Facades\Notification;
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
        ->assertSee('Tanggal otomatis disesuaikan dengan hari jadwal kelas.')
        ->assertSee('data-day-of-week', false)
        ->assertSee('adminBookingForm', false);

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
    $cancelable = AdminFixture::enrollment($member, overrides: ['status' => 'booked']);
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
