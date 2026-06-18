<?php

use App\Models\ClassEnrollment;
use App\Models\ClassSchedule;
use App\Models\GymClass;
use App\Models\Member;
use App\Models\MemberPackageSession;
use App\Models\Membership;
use App\Models\Package as ServicePackage;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Trainer;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

function createAdminImprovementsUser(): User
{
    $user = User::factory()->create([
        'name' => 'Admin Improvements',
        'email' => 'admin.improvements@example.com',
    ]);
    $user->assignRole('admin');

    return $user;
}

function createAdminImprovementsMember(string $code = 'PG-ADMIN-IMP-0001'): array
{
    $user = User::factory()->create([
        'name' => 'Member Improvements Admin',
        'email' => fake()->unique()->safeEmail(),
        'phone' => '0812'.fake()->unique()->numerify('########'),
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

test('admin reject payment form exposes accessible label and confirmation', function () {
    $admin = createAdminImprovementsUser();
    [, $member] = createAdminImprovementsMember('PG-ADMIN-IMP-REJECT');

    $package = ServicePackage::create([
        'name' => 'Gym Reject Improvements',
        'slug' => 'gym-reject-improvements',
        'package_kind' => 'membership',
        'type' => 'gym',
        'price' => 250000,
        'duration_days' => 30,
        'is_active' => true,
    ]);

    $membership = Membership::create([
        'member_id' => $member->id,
        'package_id' => $package->id,
        'code' => 'MBR-IMP-REJECT-0001',
        'start_date' => now()->toDateString(),
        'end_date' => now()->addMonth()->toDateString(),
        'price' => 250000,
        'status' => 'pending_payment',
    ]);

    Payment::create([
        'payment_code' => 'PAY-IMP-REJECT-0001',
        'member_id' => $member->id,
        'payable_type' => Membership::class,
        'payable_id' => $membership->id,
        'method' => 'midtrans',
        'amount' => 250000,
        'status' => 'waiting_payment',
    ]);

    $this->actingAs($admin)->get('/admin/pembayaran')
        ->assertOk()
        ->assertSee('Alasan penolakan pembayaran PAY-IMP-REJECT-0001')
        ->assertSee('Tolak pembayaran PAY-IMP-REJECT-0001')
        ->assertSee('role="dialog"', false)
        ->assertSee('admin-button-danger', false);
});

test('admin recent payments and members use semantic status pill classes', function () {
    $admin = createAdminImprovementsUser();
    [, $member] = createAdminImprovementsMember('PG-ADMIN-IMP-PILL');

    $package = ServicePackage::create([
        'name' => 'Gym Pill Improvements',
        'slug' => 'gym-pill-improvements',
        'package_kind' => 'membership',
        'type' => 'gym',
        'price' => 250000,
        'duration_days' => 30,
        'is_active' => true,
    ]);

    $membership = Membership::create([
        'member_id' => $member->id,
        'package_id' => $package->id,
        'code' => 'MBR-IMP-PILL-0001',
        'start_date' => now()->toDateString(),
        'end_date' => now()->addMonth()->toDateString(),
        'price' => 250000,
        'status' => 'pending_payment',
    ]);

    Payment::create([
        'payment_code' => 'PAY-IMP-PILL-0001',
        'member_id' => $member->id,
        'payable_type' => Membership::class,
        'payable_id' => $membership->id,
        'method' => 'midtrans',
        'amount' => 250000,
        'status' => 'waiting_payment',
    ]);

    $this->actingAs($admin)->get('/admin')
        ->assertOk()
        ->assertSee('admin-status-warning', false)
        ->assertSee('admin-status-success', false);
});

test('admin booking widget renders status as semantic pill', function () {
    $admin = createAdminImprovementsUser();
    [, $member] = createAdminImprovementsMember('PG-ADMIN-IMP-BOOKING');

    $gymClass = GymClass::create([
        'name' => 'Aerobic Pill Admin',
        'slug' => 'aerobic-pill-admin',
        'class_type' => 'senam',
        'access_type' => 'included',
        'required_package_type' => 'senam',
        'capacity' => 20,
        'is_active' => true,
    ]);

    $schedule = ClassSchedule::create([
        'gym_class_id' => $gymClass->id,
        'day_of_week' => (int) now()->dayOfWeekIso,
        'start_time' => '17:00:00',
        'end_time' => '18:00:00',
        'capacity' => 20,
        'is_active' => true,
    ]);

    ClassEnrollment::create([
        'schedule_id' => $schedule->id,
        'member_id' => $member->id,
        'session_date' => now()->toDateString(),
        'status' => 'booked',
    ]);

    $this->actingAs($admin)->get('/admin/booking')
        ->assertOk()
        ->assertSee('admin-status-info', false)
        ->assertSee('Batalkan booking', false)
        ->assertSee('role="dialog"', false)
        ->assertSee('admin-button-danger', false);
});

test('admin product edit form shows current image preview and aria-invalid on error', function () {
    $admin = createAdminImprovementsUser();

    $product = Product::create([
        'name' => 'Produk Preview Admin',
        'slug' => 'produk-preview-admin',
        'price' => 120000,
        'stock' => 10,
        'is_active' => true,
        'image_path' => 'storage/images/admin/uploads/sample-product.jpg',
    ]);

    $this->actingAs($admin)->get(route('admin.resources.edit', ['resource' => 'products', 'id' => $product->id]))
        ->assertOk()
        ->assertSee('File saat ini')
        ->assertSee('Pilih file baru untuk mengganti');

    // Submitting an invalid update (blank required name) should mark the field aria-invalid.
    $this->actingAs($admin)
        ->from(route('admin.resources.edit', ['resource' => 'products', 'id' => $product->id]))
        ->patch(route('admin.resources.update', ['resource' => 'products', 'id' => $product->id]), [
            'name' => '',
            'price' => 120000,
            'stock' => 10,
        ])
        ->assertRedirect();
});

test('admin sidebar shows account identity block', function () {
    $admin = createAdminImprovementsUser();

    $this->actingAs($admin)->get('/admin')
        ->assertOk()
        ->assertSee('Admin Improvements')
        ->assertDontSee('Ganti Password');
});

test('admin profile page links to account security edit', function () {
    $admin = createAdminImprovementsUser();

    $this->actingAs($admin)->get('/admin/profil')
        ->assertOk()
        ->assertSee('Edit Akun Saya')
        ->assertSee(route('profile.edit'), false);
});

test('admin reports export link reflects filtered period via alpine binding', function () {
    $admin = createAdminImprovementsUser();

    $this->actingAs($admin)->get('/admin/laporan?date_from=2026-01-01&date_to=2026-01-31')
        ->assertOk()
        ->assertSee('encodeURIComponent(dateFrom)', false)
        ->assertSee('Export CSV');
});

test('admin pending payment badge is visible with accessible label', function () {
    $admin = createAdminImprovementsUser();

    $this->actingAs($admin)->get('/admin')
        ->assertOk()
        ->assertSee('pembayaran menunggu verifikasi', false);
});

test('admin settings save form requires confirmation', function () {
    $admin = createAdminImprovementsUser();

    $this->actingAs($admin)->get('/admin/pengaturan')
        ->assertOk()
        ->assertSee('Simpan perubahan pengaturan website', false);
});

test('admin notifications page renders semantic success status pill', function () {
    $admin = createAdminImprovementsUser();

    $this->actingAs($admin)->get('/admin/notifikasi')
        ->assertOk()
        ->assertSee('Kesiapan Notifikasi')
        ->assertSee('Membership')
        ->assertSee('Siap')
        ->assertSee('admin-status-success', false);
});

test('admin trainer page shows active member recap and capacity status', function () {
    $admin = createAdminImprovementsUser();

    $package = ServicePackage::create([
        'name' => 'PT Capacity Improvements',
        'slug' => 'pt-capacity-improvements',
        'package_kind' => 'personal_trainer',
        'type' => 'pt',
        'price' => 650000,
        'session_count' => 5,
        'is_active' => true,
    ]);

    $availableTrainer = Trainer::create(['name' => 'Coach Available', 'specialization' => 'PT', 'is_active' => true]);
    $nearlyFullTrainer = Trainer::create(['name' => 'Coach Nearly Full', 'specialization' => 'PT', 'is_active' => true]);
    $fullTrainer = Trainer::create(['name' => 'Coach Full', 'specialization' => 'PT', 'is_active' => true]);
    Trainer::create(['name' => 'Coach Empty', 'specialization' => 'PT', 'is_active' => true]);

    $memberSequence = 1;
    $sessionSequence = 1;
    $createMember = function (string $status = 'active') use (&$memberSequence): Member {
        [, $member] = createAdminImprovementsMember('PG-TRAINER-CAP-'.str_pad((string) $memberSequence, 4, '0', STR_PAD_LEFT));
        $memberSequence++;

        if ($status !== 'active') {
            $member->forceFill(['status' => $status])->save();
        }

        return $member;
    };
    $createSession = function (Trainer $trainer, Member $member, string $status = 'active', int $remainingSessions = 3, ?string $expiredAt = null) use ($package, &$sessionSequence): void {
        MemberPackageSession::create([
            'member_id' => $member->id,
            'package_id' => $package->id,
            'trainer_id' => $trainer->id,
            'code' => 'MPS-CAP-'.str_pad((string) $sessionSequence, 4, '0', STR_PAD_LEFT),
            'total_sessions' => 5,
            'used_sessions' => max(0, 5 - $remainingSessions),
            'remaining_sessions' => $remainingSessions,
            'price' => 650000,
            'started_at' => now()->subDay()->toDateString(),
            'expired_at' => $expiredAt,
            'status' => $status,
        ]);
        $sessionSequence++;
    };

    foreach (range(1, 2) as $_) {
        $createSession($availableTrainer, $createMember());
    }

    $duplicateMember = $createMember();
    $createSession($availableTrainer, $duplicateMember);
    $createSession($availableTrainer, $duplicateMember);
    $createSession($availableTrainer, $createMember('inactive'));
    $createSession($availableTrainer, $createMember(), 'expired');
    $createSession($availableTrainer, $createMember(), 'active', 0);
    $createSession($availableTrainer, $createMember(), 'active', 3, now()->subDay()->toDateString());

    foreach (range(1, 14) as $_) {
        $createSession($nearlyFullTrainer, $createMember());
    }

    foreach (range(1, 20) as $_) {
        $createSession($fullTrainer, $createMember());
    }

    $this->actingAs($admin)->get('/admin/trainer')
        ->assertOk()
        ->assertSee('Member Aktif')
        ->assertSee('Kapasitas')
        ->assertSee('Status Kapasitas')
        ->assertSeeInOrder(['Coach Full', '20', 'Penuh'])
        ->assertSeeInOrder(['Coach Nearly Full', '14', 'Hampir Penuh'])
        ->assertSeeInOrder(['Coach Available', '3', 'Tersedia'])
        ->assertSeeInOrder(['Coach Empty', '0', 'Tersedia'])
        ->assertSee('admin-status-danger', false)
        ->assertSee('admin-status-warning', false)
        ->assertSee('admin-status-success', false);
});

test('admin reports include trainer capacity recap rows', function () {
    $admin = createAdminImprovementsUser();

    Trainer::create(['name' => 'Coach Report Available', 'specialization' => 'PT', 'is_active' => true]);

    $this->actingAs($admin)->get('/admin/laporan')
        ->assertOk()
        ->assertSee('Trainer tersedia')
        ->assertSee('Trainer hampir penuh')
        ->assertSee('Trainer penuh');
});

test('admin layout renders success flash banner with emerald palette and polite live region', function () {
    $admin = createAdminImprovementsUser();

    $this->actingAs($admin)
        ->withSession(['status' => 'Aksi tersimpan dengan baik.', 'status_kind' => 'success'])
        ->get('/admin')
        ->assertOk()
        ->assertSee('Aksi tersimpan dengan baik.')
        ->assertSee('border-emerald-500/30', false)
        ->assertSee('aria-live="polite"', false);
});

test('admin layout renders error flash banner with red palette and assertive live region', function () {
    $admin = createAdminImprovementsUser();

    $this->actingAs($admin)
        ->withSession(['status' => 'Aksi gagal diproses.', 'status_kind' => 'error'])
        ->get('/admin')
        ->assertOk()
        ->assertSee('Aksi gagal diproses.')
        ->assertSee('border-red-500/30', false)
        ->assertSee('aria-live="assertive"', false);
});

test('admin profile edit dispatches to admin account security page using admin layout', function () {
    $admin = createAdminImprovementsUser();

    $this->actingAs($admin)->get(route('profile.edit'))
        ->assertOk()
        ->assertSee('Keamanan Akun Admin')
        ->assertSee('Informasi Akun')
        ->assertSee('Ubah Password')
        ->assertSee('admin-card', false)
        ->assertSee('admin-form-input', false)
        ->assertSee('Kembali ke Profil Admin');
});

test('admin check-in page exposes camera scan region and hidden manual token fallback', function () {
    $admin = createAdminImprovementsUser();

    $this->actingAs($admin)->get('/admin/check-in')
        ->assertOk()
        ->assertSee('Check-in member via kamera')
        ->assertSee('id="admin-qr-camera-region"', false)
        ->assertSee('id="admin-qr-camera-start"', false)
        ->assertSee('id="admin-qr-scan-form"', false)
        ->assertSee('id="admin-qr-camera-secure-banner"', false)
        ->assertSee('Mulai Kamera')
        ->assertSee('Input Token Manual')
        ->assertSee('Check-in manual');
});
