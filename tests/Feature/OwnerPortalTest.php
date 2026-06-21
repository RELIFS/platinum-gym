<?php

use App\Models\ClassEnrollment;
use App\Models\ClassSchedule;
use App\Models\GymClass;
use App\Models\Member;
use App\Models\Membership;
use App\Models\Package as ServicePackage;
use App\Models\Payment;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

function createOwnerPortalUser(): User
{
    $user = User::factory()->create([
        'name' => 'Owner Portal',
        'email' => 'owner.portal@example.com',
    ]);
    $user->assignRole('owner');

    return $user;
}

function createOwnerPortalMember(string $code = 'PG-OWNER-0001'): array
{
    $user = User::factory()->create([
        'name' => 'Member Owner Test',
        'email' => fake()->unique()->safeEmail(),
    ]);
    $user->assignRole('member');

    $member = Member::create([
        'user_id' => $user->id,
        'member_code' => $code,
        'gender' => 'male',
        'birth_date' => '2000-01-01',
        'joined_at' => now()->subMonth()->toDateString(),
        'status' => 'active',
    ]);

    return [$user, $member];
}

test('owner portal routes require authentication', function (string $path) {
    $this->get($path)->assertRedirect('/login');
})->with([
    '/owner',
    '/owner/laporan',
    '/owner/laporan/keuangan',
    '/owner/laporan/member',
    '/owner/laporan/booking-kelas',
    '/owner/laporan/export',
]);

test('non owner roles cannot access owner portal', function (string $role) {
    $user = User::factory()->create();
    $user->assignRole($role);

    $this->actingAs($user)->get('/owner')->assertForbidden();
})->with(['member', 'admin']);

test('owner dashboard renders real business monitoring data', function () {
    $owner = createOwnerPortalUser();
    [, $member] = createOwnerPortalMember('PG-OWNER-DATA');

    $package = ServicePackage::create([
        'name' => 'Membership Owner Test',
        'slug' => 'membership-owner-test',
        'package_kind' => 'membership',
        'type' => 'gym',
        'price' => 250000,
        'duration_days' => 30,
        'is_active' => true,
    ]);

    $membership = Membership::create([
        'member_id' => $member->id,
        'package_id' => $package->id,
        'code' => 'MBR-OWNER-0001',
        'start_date' => now()->subDay()->toDateString(),
        'end_date' => now()->addMonth()->toDateString(),
        'price' => 250000,
        'status' => 'active',
    ]);

    Payment::create([
        'payment_code' => 'PAY-OWNER-PAID',
        'member_id' => $member->id,
        'payable_type' => Membership::class,
        'payable_id' => $membership->id,
        'method' => 'cash',
        'amount' => 250000,
        'status' => 'paid',
        'paid_at' => now()->subDay(),
        'midtrans_snap_token' => 'secret-snap-token',
        'midtrans_raw_response' => ['secret' => 'raw-response'],
    ]);

    Payment::create([
        'payment_code' => 'PAY-OWNER-PENDING',
        'member_id' => $member->id,
        'payable_type' => Membership::class,
        'payable_id' => $membership->id,
        'method' => 'midtrans',
        'amount' => 500000,
        'status' => 'waiting_confirmation',
    ]);

    $gymClass = GymClass::create([
        'name' => 'Yoga Owner',
        'slug' => 'yoga-owner',
        'class_type' => 'yoga',
        'access_type' => 'membership',
        'capacity' => 20,
        'is_active' => true,
    ]);

    $schedule = ClassSchedule::create([
        'gym_class_id' => $gymClass->id,
        'day_of_week' => 1,
        'start_time' => '08:00:00',
        'end_time' => '09:00:00',
        'capacity' => 20,
        'is_active' => true,
    ]);

    ClassEnrollment::create([
        'schedule_id' => $schedule->id,
        'member_id' => $member->id,
        'session_date' => now()->toDateString(),
        'status' => 'booked',
    ]);

    $this->actingAs($owner)->get('/owner')
        ->assertOk()
        ->assertSee('Dashboard Owner')
        ->assertSee('Ringkasan bisnis')
        ->assertSee('Pendapatan periode ini')
        ->assertSee('Rp 250.000')
        ->assertSee('Transaksi terkonfirmasi')
        ->assertSee('Member aktif')
        ->assertSee('Membership aktif')
        ->assertSee('Booking periode ini')
        ->assertSee('Tren pendapatan')
        ->assertSee('id="owner-business-trend-chart"', false)
        ->assertSee('id="owner-business-trend-data"', false)
        ->assertSee('PAY-OWNER-PAID')
        ->assertDontSee('Rp 750.000')
        ->assertDontSee('secret-snap-token')
        ->assertDontSee('raw-response')
        ->assertDontSee('Laba bersih');
});

test('owner reports filter data and export csv without pending revenue', function () {
    $owner = createOwnerPortalUser();
    [, $member] = createOwnerPortalMember('PG-OWNER-REPORT');

    $package = ServicePackage::create([
        'name' => 'Report Package',
        'slug' => 'report-package',
        'package_kind' => 'membership',
        'type' => 'gym',
        'price' => 200000,
        'duration_days' => 30,
        'is_active' => true,
    ]);

    $membership = Membership::create([
        'member_id' => $member->id,
        'package_id' => $package->id,
        'code' => 'MBR-OWNER-REPORT',
        'start_date' => now()->subDay()->toDateString(),
        'end_date' => now()->addMonth()->toDateString(),
        'price' => 200000,
        'status' => 'active',
    ]);

    Payment::create([
        'payment_code' => 'PAY-OWNER-REPORT-PAID',
        'member_id' => $member->id,
        'payable_type' => Membership::class,
        'payable_id' => $membership->id,
        'method' => 'cash',
        'amount' => 200000,
        'status' => 'paid',
        'paid_at' => now(),
    ]);

    Payment::create([
        'payment_code' => 'PAY-OWNER-REPORT-PENDING',
        'member_id' => $member->id,
        'payable_type' => Membership::class,
        'payable_id' => $membership->id,
        'method' => 'cash',
        'amount' => 900000,
        'status' => 'waiting_confirmation',
    ]);

    $this->actingAs($owner)->get(route('owner.reports.finance', [
        'date_from' => now()->startOfMonth()->toDateString(),
        'date_to' => now()->toDateString(),
    ]))
        ->assertOk()
        ->assertSee('Laporan Keuangan')
        ->assertSee('PAY-OWNER-REPORT-PAID')
        ->assertSee('Rp 200.000')
        ->assertDontSee('PAY-OWNER-REPORT-PENDING')
        ->assertDontSee('Rp 1.100.000')
        ->assertSee('Unduh CSV');

    $response = $this->actingAs($owner)->get(route('owner.reports.export', [
        'report_type' => 'finance',
        'date_from' => now()->startOfMonth()->toDateString(),
        'date_to' => now()->toDateString(),
    ]));

    $response->assertOk()
        ->assertHeader('content-type', 'text/csv; charset=UTF-8');

    expect($response->streamedContent())
        ->toContain('PAY-OWNER-REPORT-PAID')
        ->not->toContain('PAY-OWNER-REPORT-PENDING');
});
