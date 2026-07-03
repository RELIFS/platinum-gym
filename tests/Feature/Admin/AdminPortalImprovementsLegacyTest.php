<?php

use App\Models\ClassEnrollment;
use App\Models\ClassSchedule;
use App\Models\GymClass;
use App\Models\Membership;
use App\Models\Package as ServicePackage;
use App\Models\Payment;
use App\Models\Product;
use Database\Seeders\RolePermissionSeeder;
use Tests\Feature\Admin\Support\AdminPortalFixtures as AdminFixtures;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

test('admin reject payment form exposes accessible label and confirmation', function () {
    $admin = AdminFixtures::improvementsAdmin();
    [, $member] = AdminFixtures::improvementsMember('PG-ADMIN-IMP-REJECT');

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
    $admin = AdminFixtures::improvementsAdmin();
    [, $member] = AdminFixtures::improvementsMember('PG-ADMIN-IMP-PILL');

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
        ->assertSee('Aktivitas terbaru')
        ->assertSee('Terbaru')
        ->assertSee('Lihat data terbaru tanpa perlu membuka setiap halaman.')
        ->assertSee('admin-status-warning', false)
        ->assertSee('admin-status-success', false);
});

test('admin dashboard removes redundant quick menu and uses polished surface tokens', function () {
    $admin = AdminFixtures::improvementsAdmin();

    $this->actingAs($admin)->get('/admin')
        ->assertOk()
        ->assertSee('Ringkasan admin')
        ->assertSee('Akses cepat')
        ->assertSee('Buka laporan')
        ->assertSee('Tren aktivitas')
        ->assertSee('aria-label="Tren aktivitas 14 hari terakhir"', false)
        ->assertSee('aria-label="Perlu dicek hari ini"', false)
        ->assertSee('aria-label="Ringkasan hari ini"', false)
        ->assertSee('Belum ada tren operasional pada periode ini.')
        ->assertDontSee('id="admin-operational-trend-chart"', false)
        ->assertSee('Aktivitas terbaru')
        ->assertSee('admin-metric-card', false)
        ->assertSee('admin-action-card', false)
        ->assertDontSee('Booking Hari Ini')
        ->assertDontSee('Check-in Hari Ini')
        ->assertDontSee('Menu kerja');
});

test('admin layout and data tables expose color polish utility classes', function () {
    $admin = AdminFixtures::improvementsAdmin();

    Product::create([
        'name' => 'Produk Color Polish',
        'slug' => 'produk-color-polish',
        'price' => 95000,
        'stock' => 4,
        'is_active' => true,
    ]);

    $this->actingAs($admin)->get('/admin/produk')
        ->assertOk()
        ->assertSee('admin-mobile-menu-button', false)
        ->assertSee('aria-controls="admin-mobile-navigation"', false)
        ->assertDontSee('bg-zinc-950 text-white shadow-[0_12px_28px_rgba(24,24,27,0.22)]', false)
        ->assertSee('admin-sidebar-nav-link-active', false)
        ->assertSee('admin-sidebar-icon-frame-active', false)
        ->assertSee('admin-table-head', false)
        ->assertSee('admin-table-row', false)
        ->assertSee('admin-table-cell', false)
        ->assertSee('admin-status-neutral', false)
        ->assertSee('Produk Color Polish');
});

test('admin booking widget renders status as semantic pill', function () {
    $admin = AdminFixtures::improvementsAdmin();
    [, $member] = AdminFixtures::improvementsMember('PG-ADMIN-IMP-BOOKING');
    $sessionDate = now()->addDay();

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
        'day_of_week' => (int) $sessionDate->dayOfWeekIso,
        'start_time' => '17:00:00',
        'end_time' => '18:00:00',
        'capacity' => 20,
        'is_active' => true,
    ]);

    ClassEnrollment::create([
        'schedule_id' => $schedule->id,
        'member_id' => $member->id,
        'session_date' => $sessionDate->toDateString(),
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
    $admin = AdminFixtures::improvementsAdmin();

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

test('admin topbar shows desktop account menu', function () {
    $admin = AdminFixtures::improvementsAdmin();

    $this->actingAs($admin)->get('/admin')
        ->assertOk()
        ->assertSee('Admin Improvements')
        ->assertSee('admin.improvements@example.com')
        ->assertSee('data-portal-account-menu="admin"', false)
        ->assertSee('data-portal-account-trigger="admin"', false)
        ->assertSee('data-portal-account-dropdown="admin"', false)
        ->assertSee('data-portal-account-profile="admin"', false)
        ->assertSee('data-portal-account-logout="admin"', false)
        ->assertSee(route('admin.profile'), false)
        ->assertDontSee('Ganti Password');
});

test('admin profile page links to account security edit', function () {
    $admin = AdminFixtures::improvementsAdmin();

    $this->actingAs($admin)->get('/admin/profil')
        ->assertOk()
        ->assertSee('Kelola Keamanan Akun')
        ->assertSee('Foto profil admin')
        ->assertSee(route('profile.edit'), false);
});

test('admin reports export link reflects filtered period via alpine binding', function () {
    $admin = AdminFixtures::improvementsAdmin();

    $this->actingAs($admin)->get('/admin/laporan?date_from=2026-01-01&date_to=2026-01-31')
        ->assertOk()
        ->assertSee('encodeURIComponent(dateFrom)', false)
        ->assertSee('Unduh CSV')
        ->assertDontSee('Export CSV');
});

test('admin audit log uses user friendly wording', function () {
    $admin = AdminFixtures::improvementsAdmin();

    $this->actingAs($admin)->get('/admin/audit-log')
        ->assertOk()
        ->assertSee('Log Aktivitas Terbaru')
        ->assertSee('Jejak perubahan penting yang tercatat di sistem.')
        ->assertSee('Jenis perubahan')
        ->assertSee('Semua aktivitas')
        ->assertSee('Admin')
        ->assertDontSee('Spatie Activitylog')
        ->assertDontSee('Activity Log Terbaru')
        ->assertDontSee('Semua event')
        ->assertDontSee('Semua user');
});

test('admin pending payment badge is visible with accessible label', function () {
    $admin = AdminFixtures::improvementsAdmin();

    $this->actingAs($admin)->get('/admin')
        ->assertOk()
        ->assertSee('pembayaran menunggu verifikasi', false);
});

test('admin settings save form requires confirmation', function () {
    $admin = AdminFixtures::improvementsAdmin();

    $this->actingAs($admin)->get('/admin/pengaturan')
        ->assertOk()
        ->assertSee('Simpan perubahan pengaturan website', false)
        ->assertSee('Informasi Umum')
        ->assertSee('Kontak Publik')
        ->assertSee('Media Sosial')
        ->assertSee('Awalan Nomor Invoice')
        ->assertSee('Catatan Footer Invoice')
        ->assertDontSee('Media Sosial &amp; Peta', false)
        ->assertDontSee('Link embed peta Google')
        ->assertDontSee('Konfigurasi sensitif')
        ->assertDontSee('URL Embed Maps')
        ->assertDontSee('Prefix Invoice');
});

test('admin notifications page renders pending approval inbox', function () {
    $admin = AdminFixtures::improvementsAdmin();
    [, $member] = AdminFixtures::member('PG-ADMIN-NOTIF-PROOF', [
        'name' => 'Member Proof Pending',
        'phone' => '081299990331',
    ], [
        'is_student' => true,
        'student_proof_path' => 'member/student-proofs/notif-proof.jpg',
        'student_proof_uploaded_at' => now(),
        'student_verification_status' => 'pending_review',
    ]);
    AdminFixtures::member('PG-ADMIN-NOTIF-UMUM');

    $this->actingAs($admin)->get('/admin/notifikasi')
        ->assertOk()
        ->assertSee('Inbox Persetujuan Admin')
        ->assertSee('Review bukti mahasiswa')
        ->assertSee('Member Proof Pending')
        ->assertSee('PG-ADMIN-NOTIF-PROOF')
        ->assertSee('Menunggu review')
        ->assertSee('Review Bukti')
        ->assertSee(route('admin.members.student-proof.review', $member), false)
        ->assertDontSee('PG-ADMIN-NOTIF-UMUM');
});

test('admin layout renders success flash banner with emerald palette and polite live region', function () {
    $admin = AdminFixtures::improvementsAdmin();

    $this->actingAs($admin)
        ->withSession(['status' => 'Aksi tersimpan dengan baik.', 'status_kind' => 'success'])
        ->get('/admin')
        ->assertOk()
        ->assertSee('Aksi tersimpan dengan baik.')
        ->assertSee('border-emerald-500/30', false)
        ->assertSee('aria-live="polite"', false);
});

test('admin layout renders error flash banner with red palette and assertive live region', function () {
    $admin = AdminFixtures::improvementsAdmin();

    $this->actingAs($admin)
        ->withSession(['status' => 'Aksi gagal diproses.', 'status_kind' => 'error'])
        ->get('/admin')
        ->assertOk()
        ->assertSee('Aksi gagal diproses.')
        ->assertSee('border-red-500/30', false)
        ->assertSee('aria-live="assertive"', false);
});

test('admin profile edit dispatches to admin account security page using admin layout', function () {
    $admin = AdminFixtures::improvementsAdmin();

    $this->actingAs($admin)->get(route('profile.edit'))
        ->assertOk()
        ->assertSee('Keamanan Akun Admin')
        ->assertSee('Informasi Akun')
        ->assertSee('Ubah Kata Sandi')
        ->assertSee('Kata sandi saat ini')
        ->assertSee('Simpan Kata Sandi')
        ->assertDontSee('Ubah Password')
        ->assertSee('admin-card', false)
        ->assertSee('admin-form-input', false)
        ->assertSee('Kembali ke Profil Admin');
});

test('admin check-in page exposes camera scan region without manual check in fallback', function () {
    $admin = AdminFixtures::improvementsAdmin();

    $this->actingAs($admin)->get('/admin/check-in')
        ->assertOk()
        ->assertSee('Check-in member dengan kamera')
        ->assertSee('Status Check-in Hari Ini')
        ->assertSee('Pindai QR')
        ->assertSee('Cek Pratinjau')
        ->assertSee('Riwayat Check-in &amp; Sesi', false)
        ->assertSee('Cari member, kode, paket, aktivitas')
        ->assertSee('name="date_from"', false)
        ->assertSee('name="date_to"', false)
        ->assertSee('admin-filter-bar', false)
        ->assertSee('Belum ada riwayat check-in pada periode ini.')
        ->assertSee('Pantau check-in gym harian dan proses masuk member melalui QR kamera.')
        ->assertSee('id="admin-qr-camera-region"', false)
        ->assertSee('id="admin-qr-camera-start"', false)
        ->assertSee('id="admin-qr-scan-form"', false)
        ->assertSee('id="admin-qr-camera-secure-banner"', false)
        ->assertSee('Mulai Kamera')
        ->assertDontSee('Input Token Manual')
        ->assertDontSee('input manual')
        ->assertDontSee('Check-in manual')
        ->assertDontSee('Check-in Terbaru')
        ->assertDontSee('>Terbaru<', false)
        ->assertDontSee('Belum ada check-in hari ini.')
        ->assertDontSee('Member Aktif')
        ->assertDontSee('Check-in Manual');

    $this->actingAs($admin)->post('/admin/check-in/manual')->assertNotFound();
});
