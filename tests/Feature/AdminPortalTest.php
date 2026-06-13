<?php

use App\Models\ClassEnrollment;
use App\Models\ClassSchedule;
use App\Models\GymCheckIn;
use App\Models\GymClass;
use App\Models\Member;
use App\Models\Membership;
use App\Models\Package as ServicePackage;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Setting;
use App\Models\Trainer;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

function createAdminPortalUser(): User
{
    $user = User::factory()->create([
        'name' => 'Admin Portal',
        'email' => 'admin.portal@example.com',
    ]);
    $user->assignRole('admin');

    return $user;
}

function createAdminPortalMember(string $code = 'PG-ADMIN-0001'): array
{
    $user = User::factory()->create([
        'name' => 'Member Admin Test',
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

test('admin portal routes require authentication', function (string $path) {
    $this->get($path)->assertRedirect('/login');
})->with([
    '/admin',
    '/admin/check-in',
    '/admin/booking',
    '/admin/notifikasi',
    '/admin/anggota',
    '/admin/paket',
    '/admin/kelas',
    '/admin/pembayaran',
    '/admin/produk',
    '/admin/galeri',
    '/admin/testimoni',
    '/admin/promo',
    '/admin/trainer',
    '/admin/laporan',
    '/admin/audit-log',
    '/admin/pengaturan',
    '/admin/profil',
]);

test('non admin roles cannot access admin portal', function (string $role) {
    $user = User::factory()->create();
    $user->assignRole($role);

    $this->actingAs($user)->get('/admin')->assertForbidden();
})->with(['member', 'owner']);

test('admin can access all admin v1 pages', function (string $path, string $title) {
    $admin = createAdminPortalUser();

    $this->actingAs($admin)->get($path)
        ->assertOk()
        ->assertSee($title)
        ->assertSee('Admin Area')
        ->assertSee('Read-only v1')
        ->assertDontSee("You're logged in!");
})->with([
    ['/admin', 'Dashboard Admin'],
    ['/admin/check-in', 'Check-in'],
    ['/admin/booking', 'Booking Kelas'],
    ['/admin/notifikasi', 'Notifikasi'],
    ['/admin/anggota', 'Anggota'],
    ['/admin/paket', 'Paket'],
    ['/admin/kelas', 'Kelas'],
    ['/admin/pembayaran', 'Pembayaran'],
    ['/admin/produk', 'Produk'],
    ['/admin/galeri', 'Galeri'],
    ['/admin/testimoni', 'Testimoni'],
    ['/admin/promo', 'Promo'],
    ['/admin/trainer', 'Trainer'],
    ['/admin/laporan', 'Laporan'],
    ['/admin/audit-log', 'Audit Log'],
    ['/admin/pengaturan', 'Pengaturan'],
    ['/admin/profil', 'Profil Admin'],
]);

test('admin dashboard and modules render operational data', function () {
    $admin = createAdminPortalUser();
    [, $member] = createAdminPortalMember('PG-ADMIN-DATA');

    $package = ServicePackage::create([
        'name' => 'Gym Admin Test',
        'slug' => 'gym-admin-test',
        'package_kind' => 'membership',
        'type' => 'gym',
        'price' => 250000,
        'duration_days' => 30,
        'is_active' => true,
    ]);

    $membership = Membership::create([
        'member_id' => $member->id,
        'package_id' => $package->id,
        'code' => 'MBR-ADMIN-0001',
        'start_date' => now()->subDay()->toDateString(),
        'end_date' => now()->addMonth()->toDateString(),
        'price' => 250000,
        'status' => 'active',
    ]);

    Payment::create([
        'payment_code' => 'PAY-ADMIN-0001',
        'member_id' => $member->id,
        'payable_type' => Membership::class,
        'payable_id' => $membership->id,
        'method' => 'transfer',
        'amount' => 250000,
        'status' => 'waiting_confirmation',
    ]);

    $trainer = Trainer::create([
        'name' => 'Coach Admin',
        'specialization' => 'Strength',
        'is_active' => true,
    ]);

    $gymClass = GymClass::create([
        'name' => 'Zumba Admin',
        'slug' => 'zumba-admin',
        'class_type' => 'zumba',
        'access_type' => 'membership',
        'capacity' => 25,
        'is_active' => true,
    ]);

    $schedule = ClassSchedule::create([
        'gym_class_id' => $gymClass->id,
        'trainer_id' => $trainer->id,
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

    GymCheckIn::create([
        'member_id' => $member->id,
        'membership_id' => $membership->id,
        'check_in_date' => now()->toDateString(),
        'check_in_at' => now(),
        'method' => 'qr',
        'scanned_by' => $admin->id,
    ]);

    Product::create([
        'name' => 'Whey Admin',
        'slug' => 'whey-admin',
        'price' => 450000,
        'stock' => 2,
        'is_active' => true,
    ]);

    $this->actingAs($admin)->get('/admin')
        ->assertOk()
        ->assertSee('Member Aktif')
        ->assertSee('Pembayaran Pending')
        ->assertSee('PAY-ADMIN-0001')
        ->assertSee('data-admin-table-search', false)
        ->assertSee('Zumba Admin')
        ->assertSee('Check-in Hari Ini');

    $this->actingAs($admin)->get('/admin/produk')
        ->assertOk()
        ->assertSee('Whey Admin')
        ->assertSee('Rp 450.000')
        ->assertSee('Aktif')
        ->assertSee('data-admin-table-search', false)
        ->assertDontSee('Tambah')
        ->assertDontSee('Edit')
        ->assertDontSee('Hapus');
});

test('admin settings page masks sensitive values', function () {
    $admin = createAdminPortalUser();

    Setting::create([
        'key' => 'gemini_api_key',
        'value' => 'secret-test-value',
        'type' => 'string',
        'group' => 'ai',
    ]);

    Setting::create([
        'key' => 'site_tagline',
        'value' => 'Gym premium di Padang',
        'type' => 'string',
        'group' => 'general',
    ]);

    $this->actingAs($admin)->get('/admin/pengaturan')
        ->assertOk()
        ->assertSee('gemini_api_key')
        ->assertSee('Tersamarkan')
        ->assertDontSee('secret-test-value')
        ->assertSee('site_tagline')
        ->assertSee('Gym premium di Padang');
});
