<?php

use App\Models\ClassEnrollment;
use App\Models\ClassSchedule;
use App\Models\GymClass;
use App\Models\Member;
use App\Models\Membership;
use App\Models\Package as ServicePackage;
use App\Models\Payment;
use App\Models\QrToken;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Support\Str;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

function createPortalMember(string $code = 'PG-PORTAL-0001'): array
{
    $user = User::factory()->create([
        'name' => 'Andi Portal',
        'email' => 'andi.portal@example.com',
        'phone' => '081234567890',
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

test('member portal routes require authentication', function (string $path) {
    $this->get($path)->assertRedirect('/login');
})->with([
    '/member/dashboard',
    '/member/profil',
    '/member/membership',
    '/member/booking-kelas',
    '/member/riwayat-booking',
    '/member/transaksi',
    '/member/qr',
    '/member/notifikasi',
    '/member/ai-assistant',
]);

test('member portal routes require complete member profile', function () {
    $user = User::factory()->create();
    $user->assignRole('member');

    $this->actingAs($user)->get('/member/membership')
        ->assertRedirect('/member/complete-profile');
});

test('admin and owner cannot access member portal', function (string $role) {
    $user = User::factory()->create();
    $user->assignRole($role);

    $this->actingAs($user)->get('/member/dashboard')->assertForbidden();
})->with(['admin', 'owner']);

test('complete member can access all member portal pages', function (string $path, string $text) {
    [$user] = createPortalMember('PG-PORTAL-ACCESS');

    $this->actingAs($user)->get($path)
        ->assertOk()
        ->assertSee($text)
        ->assertSee('Member Area');
})->with([
    ['/member/dashboard', 'Dashboard Member'],
    ['/member/profil', 'Profil Member'],
    ['/member/membership', 'Membership'],
    ['/member/booking-kelas', 'Booking Kelas'],
    ['/member/riwayat-booking', 'Riwayat Booking'],
    ['/member/transaksi', 'Transaksi'],
    ['/member/qr', 'QR Member'],
    ['/member/notifikasi', 'Notifikasi'],
    ['/member/ai-assistant', 'AI Assistant'],
]);

test('dashboard renders real member data and empty operational states', function () {
    [$user] = createPortalMember('PG-PORTAL-REAL');

    $this->actingAs($user)->get('/member/dashboard')
        ->assertOk()
        ->assertSee('Andi Portal')
        ->assertSee('PG-PORTAL-REAL')
        ->assertSee('Belum ada membership aktif')
        ->assertSee('Belum ada booking mendatang')
        ->assertSee('Belum ada transaksi')
        ->assertSee('Siap diterbitkan');
});

test('dashboard renders membership payment booking and qr summaries', function () {
    [$user, $member] = createPortalMember('PG-PORTAL-DATA');

    $package = ServicePackage::create([
        'name' => 'Gym Umum Test',
        'slug' => 'gym-umum-test',
        'package_kind' => 'membership',
        'type' => 'gym',
        'price' => 249000,
        'duration_days' => 30,
        'is_active' => true,
    ]);

    $membership = Membership::create([
        'member_id' => $member->id,
        'package_id' => $package->id,
        'code' => 'MBR-PORTAL-0001',
        'start_date' => now()->subDay()->toDateString(),
        'end_date' => now()->addMonth()->toDateString(),
        'price' => 249000,
        'status' => 'active',
    ]);

    Payment::create([
        'payment_code' => 'PAY-PORTAL-0001',
        'member_id' => $member->id,
        'payable_type' => Membership::class,
        'payable_id' => $membership->id,
        'method' => 'transfer',
        'amount' => 249000,
        'status' => 'waiting_confirmation',
    ]);

    $gymClass = GymClass::create([
        'name' => 'Zumba Portal',
        'slug' => 'zumba-portal',
        'class_type' => 'zumba',
        'access_type' => 'membership',
        'capacity' => 25,
        'is_active' => true,
    ]);

    $schedule = ClassSchedule::create([
        'gym_class_id' => $gymClass->id,
        'day_of_week' => 1,
        'start_time' => '08:00:00',
        'end_time' => '09:00:00',
        'is_active' => true,
    ]);

    ClassEnrollment::create([
        'schedule_id' => $schedule->id,
        'member_id' => $member->id,
        'session_date' => now()->addDay()->toDateString(),
        'status' => 'booked',
    ]);

    $token = Str::random(64);

    QrToken::create([
        'tokenable_type' => Member::class,
        'tokenable_id' => $member->id,
        'token' => $token,
        'purpose' => 'member',
    ]);

    $this->actingAs($user)->get('/member/dashboard')
        ->assertOk()
        ->assertSee('Gym Umum Test')
        ->assertSee('Zumba Portal')
        ->assertSee('PAY-PORTAL-0001')
        ->assertSee('Token aktif')
        ->assertDontSee($token);
});
