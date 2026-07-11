<?php

use App\Models\ClassAttendance;
use App\Models\ClassEnrollment;
use App\Models\ClassSchedule;
use App\Models\GymCheckIn;
use App\Models\GymClass;
use App\Models\Invoice;
use App\Models\Member;
use App\Models\MemberPackageSession;
use App\Models\MemberPackageSessionUsage;
use App\Models\Membership;
use App\Models\Package as ServicePackage;
use App\Models\Payment;
use App\Models\Product;
use App\Models\QrToken;
use App\Models\Setting;
use App\Models\Trainer;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Support\Carbon;
use Tests\Feature\Admin\Support\AdminPortalFixtures as AdminFixtures;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

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

test('admin can access all admin pages', function (string $path, string $title) {
    $admin = AdminFixtures::admin(['name' => 'Admin Portal']);

    $this->actingAs($admin)->get($path)
        ->assertOk()
        ->assertSee($title)
        ->assertSee('Keluar')
        ->assertDontSee('Admin Area')
        ->assertDontSee('CRUD Operasional')
        ->assertDontSee('Preview publik')
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

test('admin member resource no longer shows legacy nim field', function () {
    $admin = AdminFixtures::admin(['name' => 'Admin Portal']);

    $this->actingAs($admin)->get(route('admin.resources.create', 'members'))
        ->assertOk()
        ->assertSee('Kategori Mahasiswa')
        ->assertDontSee('NIM')
        ->assertDontSee('Nomor Identitas Mahasiswa');
});

test('admin resource forms use production ready field wording', function () {
    $admin = AdminFixtures::admin(['name' => 'Admin Portal']);

    $this->actingAs($admin)->get(route('admin.resources.create', 'packages'))
        ->assertOk()
        ->assertSee('Slug URL')
        ->assertSee('Batas Jenis Kelamin')
        ->assertSee('Wajib Membership Aktif')
        ->assertSee('Manfaat Paket')
        ->assertDontSee('Butuh Membership Aktif')
        ->assertDontSee('Benefit');

    $this->actingAs($admin)->get(route('admin.resources.create', 'products'))
        ->assertOk()
        ->assertSee('Slug URL')
        ->assertSee('Deskripsi Foto')
        ->assertDontSee('Alt Foto');
});

test('admin dashboard and modules render operational data', function () {
    $admin = AdminFixtures::admin(['name' => 'Admin Portal']);
    [, $member] = AdminFixtures::member('PG-ADMIN-DATA');

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

    Payment::create([
        'payment_code' => 'PAY-ADMIN-PAID-TREND',
        'member_id' => $member->id,
        'payable_type' => Membership::class,
        'payable_id' => $membership->id,
        'method' => 'cash',
        'amount' => 250000,
        'status' => 'paid',
        'paid_at' => now()->subDay(),
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
        ->assertSee('Ringkasan admin')
        ->assertSee('Pantau aktivitas gym, pembayaran, booking, check-in, dan data penting lainnya dari satu halaman.')
        ->assertSee('aria-label="Perlu dicek hari ini"', false)
        ->assertSee('aria-label="Ringkasan hari ini"', false)
        ->assertSee('Member Aktif')
        ->assertSee('Menunggu Pembayaran')
        ->assertSee('Booking hari ini')
        ->assertSee('Tren aktivitas')
        ->assertSee('Lihat perkembangan check-in, booking, dan pembayaran yang sudah dikonfirmasi selama 14 hari terakhir.')
        ->assertSee('aria-label="Tren aktivitas 14 hari terakhir"', false)
        ->assertSee('id="admin-operational-trend-chart"', false)
        ->assertSee('id="admin-operational-trend-data"', false)
        ->assertSee('type="application/json"', false)
        ->assertSee('role="img"', false)
        ->assertSee('"series"', false)
        ->assertSee('"labels"', false)
        ->assertSee('"color"', false)
        ->assertSee('Pembayaran: 1')
        ->assertSee('Akses cepat')
        ->assertSee('Buka laporan')
        ->assertSee('Aktivitas terbaru')
        ->assertSee('admin-metric-card', false)
        ->assertSee('admin-action-card', false)
        ->assertSee('PAY-ADMIN-0001')
        ->assertDontSee('Zumba Admin')
        ->assertDontSee('Booking Hari Ini')
        ->assertDontSee('Check-in Hari Ini')
        ->assertDontSee('Menu kerja');

    $this->actingAs($admin)->get('/admin/produk')
        ->assertOk()
        ->assertSee('Whey Admin')
        ->assertSee('Rp 450.000')
        ->assertSee('Aktif')
        ->assertSee('data-admin-table-search', false)
        ->assertSee('Tambah Produk')
        ->assertSee('Edit')
        ->assertDontSee('Hapus');
});

test('admin settings page masks sensitive values', function () {
    $admin = AdminFixtures::admin(['name' => 'Admin Portal']);

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
        ->assertDontSee('secret-test-value')
        ->assertDontSee('gemini_api_key')
        ->assertDontSee('Tersamarkan')
        ->assertDontSee('site_tagline')
        ->assertDontSee('Gym premium di Padang')
        ->assertDontSee('Kunci')
        ->assertDontSee('URL Google Maps')
        ->assertDontSee('Link embed peta Google');
});

test('admin check-in page renders paginated history with date range filters', function () {
    Carbon::setTestNow(Carbon::parse('2026-06-30 12:00:00'));

    try {
        $admin = AdminFixtures::admin(['name' => 'Admin Portal']);

        $package = ServicePackage::create([
            'name' => 'Gym History Test',
            'slug' => 'gym-history-test',
            'package_kind' => 'membership',
            'type' => 'gym',
            'price' => 250000,
            'duration_days' => 30,
            'is_active' => true,
        ]);

        $sessionPackage = ServicePackage::create([
            'name' => 'Muaythai History Session 4x',
            'slug' => 'muaythai-history-session-4x',
            'package_kind' => 'session',
            'type' => 'muaythai',
            'price' => 400000,
            'session_count' => 4,
            'is_active' => true,
        ]);

        $extraSessionPackage = ServicePackage::create([
            'name' => 'Personal Trainer History Session 2x',
            'slug' => 'personal-trainer-history-session-2x',
            'package_kind' => 'session',
            'type' => 'pt',
            'price' => 300000,
            'session_count' => 2,
            'is_active' => true,
        ]);

        $standaloneSessionPackage = ServicePackage::create([
            'name' => 'Poundfit Standalone Session 1x',
            'slug' => 'poundfit-standalone-session-1x',
            'package_kind' => 'session',
            'type' => 'poundfit',
            'price' => 50000,
            'session_count' => 1,
            'is_active' => true,
        ]);

        foreach (range(1, 18) as $index) {
            [$user, $member] = AdminFixtures::member('PG-HISTORY-'.str_pad((string) $index, 2, '0', STR_PAD_LEFT));
            $user->forceFill(['name' => 'History Member '.str_pad((string) $index, 2, '0', STR_PAD_LEFT)])->save();

            $membership = Membership::create([
                'member_id' => $member->id,
                'package_id' => $package->id,
                'code' => 'MBR-HISTORY-'.str_pad((string) $index, 4, '0', STR_PAD_LEFT),
                'start_date' => now()->startOfMonth()->toDateString(),
                'end_date' => now()->addMonth()->toDateString(),
                'price' => 250000,
                'status' => 'active',
            ]);

            $checkIn = GymCheckIn::create([
                'member_id' => $member->id,
                'membership_id' => $membership->id,
                'check_in_date' => now()->subDays($index % 5)->toDateString(),
                'check_in_at' => now()->subMinutes($index),
                'method' => 'qr',
                'scanned_by' => $admin->id,
            ]);

            if ($index === 1) {
                $packageSession = MemberPackageSession::create([
                    'member_id' => $member->id,
                    'package_id' => $sessionPackage->id,
                    'code' => 'MPS-HISTORY-0001',
                    'total_sessions' => 4,
                    'used_sessions' => 1,
                    'remaining_sessions' => 3,
                    'price' => 400000,
                    'started_at' => now()->subDay()->toDateString(),
                    'expired_at' => now()->addMonth()->toDateString(),
                    'status' => 'active',
                ]);

                MemberPackageSessionUsage::create([
                    'member_package_session_id' => $packageSession->id,
                    'member_id' => $member->id,
                    'gym_check_in_id' => $checkIn->id,
                    'usage_date' => $checkIn->check_in_date,
                    'used_at' => $checkIn->check_in_at,
                    'method' => 'qr',
                    'recorded_by' => $admin->id,
                    'request_key' => 'history-session-usage-0001',
                ]);

                $extraPackageSession = MemberPackageSession::create([
                    'member_id' => $member->id,
                    'package_id' => $extraSessionPackage->id,
                    'code' => 'MPS-HISTORY-0002',
                    'total_sessions' => 2,
                    'used_sessions' => 1,
                    'remaining_sessions' => 1,
                    'price' => 300000,
                    'started_at' => now()->subDay()->toDateString(),
                    'expired_at' => now()->addMonth()->toDateString(),
                    'status' => 'active',
                ]);

                MemberPackageSessionUsage::create([
                    'member_package_session_id' => $extraPackageSession->id,
                    'member_id' => $member->id,
                    'gym_check_in_id' => $checkIn->id,
                    'usage_date' => $checkIn->check_in_date,
                    'used_at' => $checkIn->check_in_at,
                    'method' => 'qr',
                    'recorded_by' => $admin->id,
                    'request_key' => 'history-session-usage-0002',
                ]);
            }
        }

        [$oldUser, $oldMember] = AdminFixtures::member('PG-HISTORY-OLD');
        $oldUser->forceFill(['name' => 'History Old Member'])->save();
        $oldMembership = Membership::create([
            'member_id' => $oldMember->id,
            'package_id' => $package->id,
            'code' => 'MBR-HISTORY-OLD',
            'start_date' => now()->subMonths(3)->toDateString(),
            'end_date' => now()->addMonth()->toDateString(),
            'price' => 250000,
            'status' => 'active',
        ]);

        GymCheckIn::create([
            'member_id' => $oldMember->id,
            'membership_id' => $oldMembership->id,
            'check_in_date' => now()->subMonths(2)->toDateString(),
            'check_in_at' => now()->subMonths(2),
            'method' => 'qr',
            'scanned_by' => $admin->id,
        ]);

        [$standaloneUser, $standaloneMember] = AdminFixtures::member('PG-HISTORY-STANDALONE');
        $standaloneUser->forceFill(['name' => 'History Standalone Session Member'])->save();
        $standalonePackageSession = MemberPackageSession::create([
            'member_id' => $standaloneMember->id,
            'package_id' => $standaloneSessionPackage->id,
            'code' => 'MPS-HISTORY-STANDALONE',
            'total_sessions' => 1,
            'used_sessions' => 1,
            'remaining_sessions' => 0,
            'price' => 50000,
            'started_at' => now()->subDay()->toDateString(),
            'expired_at' => now()->addMonth()->toDateString(),
            'status' => 'active',
        ]);

        MemberPackageSessionUsage::create([
            'member_package_session_id' => $standalonePackageSession->id,
            'member_id' => $standaloneMember->id,
            'gym_check_in_id' => null,
            'usage_date' => now()->toDateString(),
            'used_at' => now()->addMinute(),
            'method' => 'qr',
            'recorded_by' => $admin->id,
            'request_key' => 'history-standalone-session-usage',
        ]);

        [$oldStandaloneUser, $oldStandaloneMember] = AdminFixtures::member('PG-HISTORY-OLD-SESSION');
        $oldStandaloneUser->forceFill(['name' => 'History Old Standalone Session Member'])->save();
        $oldStandalonePackageSession = MemberPackageSession::create([
            'member_id' => $oldStandaloneMember->id,
            'package_id' => $standaloneSessionPackage->id,
            'code' => 'MPS-HISTORY-OLD-STANDALONE',
            'total_sessions' => 1,
            'used_sessions' => 1,
            'remaining_sessions' => 0,
            'price' => 50000,
            'started_at' => now()->subMonths(3)->toDateString(),
            'expired_at' => now()->addMonth()->toDateString(),
            'status' => 'active',
        ]);

        MemberPackageSessionUsage::create([
            'member_package_session_id' => $oldStandalonePackageSession->id,
            'member_id' => $oldStandaloneMember->id,
            'gym_check_in_id' => null,
            'usage_date' => now()->subMonths(2)->toDateString(),
            'used_at' => now()->subMonths(2),
            'method' => 'qr',
            'recorded_by' => $admin->id,
            'request_key' => 'history-old-standalone-session-usage',
        ]);

        $this->actingAs($admin)->get(route('admin.check-in'))
            ->assertOk()
            ->assertSee('Riwayat Check-in &amp; Sesi', false)
            ->assertSee('Data check-in dan penggunaan sesi member dari QR kamera dan aksi admin terkonfirmasi.')
            ->assertSee('name="date_from"', false)
            ->assertSee('name="date_to"', false)
            ->assertSee(now()->startOfMonth()->toDateString())
            ->assertSee(now()->toDateString())
            ->assertSee('Menampilkan')
            ->assertSee('Berikutnya')
            ->assertSee('Paket')
            ->assertSee('Sisa Sesi')
            ->assertSee('Aktivitas')
            ->assertSee('Gym History Test')
            ->assertSee('Gym History Test + Muaythai History Session 4x + Personal Trainer History Session 2x')
            ->assertSee('Muaythai History Session 4x')
            ->assertSee('Muaythai History Session 4x: 3 sesi')
            ->assertSee('Personal Trainer History Session 2x: 1 sesi')
            ->assertSee('Poundfit Standalone Session 1x')
            ->assertSee('0 sesi')
            ->assertSee('History Member 01')
            ->assertSee('History Standalone Session Member')
            ->assertSee('Check-in + Sesi')
            ->assertSee('Check-in')
            ->assertSee('Sesi')
            ->assertDontSee('History Old Member')
            ->assertDontSee('History Old Standalone Session Member')
            ->assertDontSee('Check-in Terbaru')
            ->assertDontSee('Pemakaian Sesi')
            ->assertDontSee('Sesi dipakai tanpa check-in')
            ->assertDontSee('<th scope="col" class="px-4 py-3">Sesi</th>', false)
            ->assertDontSee('>Metode<', false)
            ->assertDontSee('>Terbaru<', false);

        $this->actingAs($admin)->get(route('admin.check-in', ['q' => 'Poundfit Standalone Session']))
            ->assertOk()
            ->assertSee('History Standalone Session Member')
            ->assertSee('Poundfit Standalone Session 1x')
            ->assertSee('0 sesi')
            ->assertSee('Sesi')
            ->assertDontSee('History Member 01');

        $this->actingAs($admin)->get(route('admin.check-in', ['q' => 'Muaythai History Session']))
            ->assertOk()
            ->assertSee('History Member 01')
            ->assertSee('Gym History Test + Muaythai History Session 4x + Personal Trainer History Session 2x')
            ->assertSee('Muaythai History Session 4x')
            ->assertSee('Muaythai History Session 4x: 3 sesi')
            ->assertDontSee('History Member 02');

        $this->actingAs($admin)->get(route('admin.check-in', [
            'date_from' => now()->subMonths(3)->startOfMonth()->toDateString(),
            'date_to' => now()->toDateString(),
            'q' => 'History Old Member',
        ]))
            ->assertOk()
            ->assertSee('History Old Member')
            ->assertDontSee('History Member 01');

        $this->actingAs($admin)->get(route('admin.check-in', [
            'date_from' => now()->subMonths(3)->startOfMonth()->toDateString(),
            'date_to' => now()->toDateString(),
            'q' => 'History Old Standalone Session Member',
        ]))
            ->assertOk()
            ->assertSee('History Old Standalone Session Member')
            ->assertSee('Poundfit Standalone Session 1x')
            ->assertSee('0 sesi')
            ->assertSee('Sesi')
            ->assertDontSee('History Member 01');
    } finally {
        Carbon::setTestNow();
    }
});

test('admin can approve and reject payments', function () {
    $admin = AdminFixtures::admin(['name' => 'Admin Portal']);
    [, $member] = AdminFixtures::member('PG-ADMIN-PAYMENT-ACTION');

    $package = ServicePackage::create([
        'name' => 'Gym Payment Action Test',
        'slug' => 'gym-payment-action-test',
        'package_kind' => 'membership',
        'type' => 'gym',
        'price' => 250000,
        'duration_days' => 30,
        'is_active' => true,
    ]);

    $membership = Membership::create([
        'member_id' => $member->id,
        'package_id' => $package->id,
        'code' => 'MBR-ADMIN-ACTION-0001',
        'start_date' => now()->toDateString(),
        'end_date' => now()->addMonth()->toDateString(),
        'price' => 250000,
        'status' => 'pending_payment',
    ]);

    $payment = Payment::create([
        'payment_code' => 'PAY-ADMIN-ACTION-0001',
        'member_id' => $member->id,
        'payable_type' => Membership::class,
        'payable_id' => $membership->id,
        'method' => 'cash',
        'amount' => 250000,
        'status' => 'waiting_confirmation',
    ]);

    $this->actingAs($admin)->post(route('admin.payments.approve', $payment))->assertRedirect();
    expect($payment->refresh()->status)->toBe('paid')
        ->and($membership->refresh()->status)->toBe('active');

    $rejectedMembership = Membership::create([
        'member_id' => $member->id,
        'package_id' => $package->id,
        'code' => 'MBR-ADMIN-REJECT-0001',
        'start_date' => now()->toDateString(),
        'end_date' => now()->addMonth()->toDateString(),
        'price' => 250000,
        'status' => 'pending_payment',
    ]);

    $rejectedPayment = Payment::create([
        'payment_code' => 'PAY-ADMIN-REJECT-0001',
        'member_id' => $member->id,
        'payable_type' => Membership::class,
        'payable_id' => $rejectedMembership->id,
        'method' => 'cash',
        'amount' => 250000,
        'status' => 'waiting_confirmation',
    ]);

    $this->actingAs($admin)->post(route('admin.payments.reject', $rejectedPayment), [
        'reason' => 'Bukti pembayaran tidak sesuai.',
    ])->assertRedirect();

    expect($rejectedPayment->refresh()->status)->toBe('rejected')
        ->and($rejectedMembership->refresh()->status)->toBe('cancelled');
});

test('admin can record cash payment and activate membership', function () {
    $admin = AdminFixtures::admin(['name' => 'Admin Portal']);
    [, $member] = AdminFixtures::member('PG-ADMIN-CASH');

    $package = ServicePackage::create([
        'name' => 'Cash Membership Test',
        'slug' => 'cash-membership-test',
        'package_kind' => 'membership',
        'type' => 'gym',
        'price' => 300000,
        'duration_days' => 30,
        'is_active' => true,
    ]);

    $this->actingAs($admin)->post(route('admin.payments.cash'), [
        'member_id' => $member->id,
        'package_id' => $package->id,
        'note' => 'Pembayaran cash test.',
    ])->assertRedirect();

    $payment = Payment::query()->where('member_id', $member->id)->where('method', 'cash')->firstOrFail();

    expect($payment->status)->toBe('paid')
        ->and($payment->amount)->toBe('300000.00')
        ->and(Membership::query()->where('member_id', $member->id)->where('status', 'active')->exists())->toBeTrue();

    Payment::create([
        'payment_code' => 'PAY-ADMIN-PENDING-COPY',
        'member_id' => $member->id,
        'payable_type' => Membership::class,
        'payable_id' => Membership::query()->where('member_id', $member->id)->firstOrFail()->id,
        'method' => 'transfer',
        'amount' => 300000,
        'status' => 'waiting_confirmation',
    ]);

    $this->actingAs($admin)->get(route('admin.payments'))
        ->assertOk()
        ->assertSee('Pembayaran Tunai')
        ->assertSee('Catat Pembayaran Tunai')
        ->assertSee('Contoh: Bukti pembayaran belum sesuai')
        ->assertDontSee('Pembayaran Cash')
        ->assertDontSee('Catat Cash');
});

test('admin booking page only exposes actions allowed by booking status', function () {
    $admin = AdminFixtures::admin(['name' => 'Admin Portal']);
    [, $member] = AdminFixtures::member('PG-ADMIN-BOOKING-ACTIONS');

    [, , $confirmed] = AdminFixtures::classBooking($member, 'confirmed');

    $this->actingAs($admin)->get(route('admin.booking'))
        ->assertOk()
        ->assertSee('Booking sudah siap untuk proses check-in.')
        ->assertDontSee('action="'.route('admin.booking.confirm', $confirmed).'"', false);

    [, , $pendingPayment] = AdminFixtures::classBooking($member, 'pending_payment');

    $this->actingAs($admin)->get(route('admin.booking'))
        ->assertOk()
        ->assertSee('Menunggu pembayaran lunas sebelum bisa dikonfirmasi.')
        ->assertDontSee('action="'.route('admin.booking.confirm', $pendingPayment).'"', false);
});

test('admin booking confirm uses guarded domain action', function () {
    $admin = AdminFixtures::admin(['name' => 'Admin Portal']);
    [, $member] = AdminFixtures::member('PG-ADMIN-BOOKING-CONFIRM');

    [, , $booked] = AdminFixtures::classBooking($member, 'booked');

    $this->actingAs($admin)->post(route('admin.booking.confirm', $booked))
        ->assertRedirect()
        ->assertSessionHas('status', 'Booking kelas berhasil dikonfirmasi.');

    expect($booked->refresh()->status)->toBe('confirmed');

    [, , $pendingPayment] = AdminFixtures::classBooking($member, 'pending_payment');

    $this->actingAs($admin)->post(route('admin.booking.confirm', $pendingPayment))
        ->assertRedirect()
        ->assertSessionHas('status_kind', 'error')
        ->assertSessionHas('status', 'Booking berbayar masih menunggu pembayaran. Konfirmasi dilakukan otomatis setelah pembayaran lunas.');

    expect($pendingPayment->refresh()->status)->toBe('pending_payment');

    [, , $attended] = AdminFixtures::classBooking($member, 'attended');

    $this->actingAs($admin)->post(route('admin.booking.confirm', $attended))
        ->assertRedirect()
        ->assertSessionHas('status_kind', 'error')
        ->assertSessionHas('status', 'Booking yang sudah tercatat hadir tidak perlu dikonfirmasi ulang.');

    expect($attended->refresh()->status)->toBe('attended');
});

test('admin booking cancel uses safe cancellation action', function () {
    $admin = AdminFixtures::admin(['name' => 'Admin Portal']);
    [, $member] = AdminFixtures::member('PG-ADMIN-BOOKING-CANCEL');
    [, , $enrollment] = AdminFixtures::classBooking($member, 'pending_payment', now()->addDay()->toDateString());

    $payment = Payment::create([
        'payment_code' => 'PAY-ADMIN-BOOKING-CANCEL',
        'member_id' => $member->id,
        'payable_type' => ClassEnrollment::class,
        'payable_id' => $enrollment->id,
        'method' => 'midtrans',
        'amount' => 50000,
        'status' => 'waiting_payment',
    ]);

    $enrollment->forceFill(['payment_id' => $payment->id])->save();

    $invoice = Invoice::create([
        'payment_id' => $payment->id,
        'invoice_number' => 'INV-ADMIN-BOOKING-CANCEL',
        'issued_at' => now()->toDateString(),
        'due_date' => now()->addDay()->toDateString(),
        'subtotal' => 50000,
        'discount' => 0,
        'tax' => 0,
        'total' => 50000,
        'status' => 'pending',
    ]);

    $this->actingAs($admin)->post(route('admin.booking.cancel', $enrollment))
        ->assertRedirect()
        ->assertSessionHas('status', 'Booking kelas berhasil dibatalkan.');

    expect($enrollment->refresh())
        ->status->toBe('cancelled')
        ->cancel_reason->toBe('Dibatalkan oleh admin.')
        ->and($payment->refresh()->status)->toBe('cancelled')
        ->and($invoice->refresh()->status)->toBe('cancelled');
});

test('admin booking cancel rejects attended and past bookings', function () {
    $admin = AdminFixtures::admin(['name' => 'Admin Portal']);
    [, $member] = AdminFixtures::member('PG-ADMIN-BOOKING-CANCEL-GUARD');

    [, $schedule, $attended] = AdminFixtures::classBooking($member, 'booked', now()->addDay()->toDateString());
    ClassAttendance::create([
        'enrollment_id' => $attended->id,
        'schedule_id' => $schedule->id,
        'member_id' => $member->id,
        'attendance_date' => now()->toDateString(),
        'attended_at' => now(),
        'method' => 'manual',
        'status' => 'present',
        'scanned_by' => $admin->id,
    ]);

    $this->actingAs($admin)->post(route('admin.booking.cancel', $attended))
        ->assertRedirect()
        ->assertSessionHas('status_kind', 'error')
        ->assertSessionHas('status', 'Booking yang sudah memiliki kehadiran tidak dapat dibatalkan.');

    expect($attended->refresh()->status)->toBe('booked');

    [, , $past] = AdminFixtures::classBooking($member, 'booked', now()->subDay()->toDateString());

    $this->actingAs($admin)->post(route('admin.booking.cancel', $past))
        ->assertRedirect()
        ->assertSessionHas('status_kind', 'error')
        ->assertSessionHas('status', 'Booking kelas yang sudah lewat tidak dapat dibatalkan.');

    expect($past->refresh()->status)->toBe('booked');
});

test('admin booking form shows synced schedule date metadata and indonesian validation', function () {
    $admin = AdminFixtures::admin(['name' => 'Admin Portal']);
    [, $member] = AdminFixtures::member('PG-ADMIN-BOOKING-FORM');
    [, $schedule] = AdminFixtures::classBooking($member);

    $this->actingAs($admin)->get(route('admin.booking'))
        ->assertOk()
        ->assertSee('Tanggal mengikuti hari jadwal kelas dan minimal 1 hari sebelum jadwal.')
        ->assertSee('Jadwal mengikuti paket aktif member.')
        ->assertSee('x-bind:data-day-of-week="schedule.day_of_week"', false)
        ->assertSee('adminBookingForm', false);

    $this->actingAs($admin)->post(route('admin.booking.store'), [
        'member_id' => $member->id,
        'schedule_id' => $schedule->id,
        'session_date' => now()->subDay()->toDateString(),
    ])
        ->assertRedirect()
        ->assertSessionHasErrors(['session_date' => 'Booking kelas minimal 1 hari sebelum jadwal.']);
});

test('admin can update whitelisted public settings without exposing secrets', function () {
    $admin = AdminFixtures::admin(['name' => 'Admin Portal']);

    Setting::create([
        'key' => 'qr_secret',
        'value' => 'do-not-render-this-secret',
        'type' => 'text',
        'group' => 'security',
    ]);

    $this->actingAs($admin)->patch(route('admin.settings.update'), [
        'site_name' => 'Platinum Gym Padang Baru',
        'address' => 'Jl. Test Admin No. 1 Padang',
        'phone_number' => '082174777761',
        'phone_display' => '+62 821-7477-7761',
        'whatsapp_number' => '6282174777761',
        'public_email' => 'info@platinumgympadang.com',
        'instagram_handle' => '@platinumgym.padang_new',
        'instagram_url' => 'https://www.instagram.com/platinumgym.padang_new',
        'maps_url' => 'https://www.google.com/maps',
        'maps_search_url' => 'https://www.google.com/maps/search/?api=1&query=Platinum%20Gym',
        'maps_shared_url' => 'https://maps.app.goo.gl/test',
        'maps_embed_url' => 'https://www.google.com/maps/embed?pb=test',
        'operational_hours_monday_saturday' => '08:00-22:00',
        'operational_hours_sunday' => 'Tutup',
        'invoice_prefix' => 'PGP',
        'invoice_footer' => 'Terima kasih.',
    ])->assertRedirect();

    $this->assertDatabaseHas('settings', [
        'key' => 'site_name',
        'value' => 'Platinum Gym Padang Baru',
    ]);

    $this->actingAs($admin)->get(route('admin.settings'))
        ->assertOk()
        ->assertSee('Platinum Gym Padang Baru')
        ->assertDontSee('do-not-render-this-secret');

    $this->actingAs($admin)->get(route('admin.settings', ['q' => 'qr_secret']))
        ->assertOk()
        ->assertDontSee('qr_secret')
        ->assertDontSee('Tersamarkan')
        ->assertDontSee('do-not-render-this-secret');
});

test('admin can filter reports and export csv', function () {
    $admin = AdminFixtures::admin(['name' => 'Admin Portal']);

    $this->actingAs($admin)->get(route('admin.reports', [
        'date_from' => now()->startOfMonth()->toDateString(),
        'date_to' => now()->toDateString(),
    ]))
        ->assertOk()
        ->assertSee('Periode operasional')
        ->assertSee('Unduh CSV')
        ->assertSee('Unduh Excel')
        ->assertSee('Unduh PDF')
        ->assertDontSee('Export CSV');

    $response = $this->actingAs($admin)->get(route('admin.reports.export', [
        'date_from' => now()->startOfMonth()->toDateString(),
        'date_to' => now()->toDateString(),
    ]));

    $response->assertOk()
        ->assertHeader('content-type', 'text/csv; charset=UTF-8');

    expect($response->streamedContent())->toContain('Metrik');

    $xlsx = $this->actingAs($admin)->get(route('admin.reports.export', [
        'format' => 'xlsx',
        'date_from' => now()->startOfMonth()->toDateString(),
        'date_to' => now()->toDateString(),
    ]));

    $xlsx->assertOk();
    expect((string) $xlsx->headers->get('content-disposition'))->toContain('.xlsx');

    $pdf = $this->actingAs($admin)->get(route('admin.reports.export', [
        'format' => 'pdf',
        'date_from' => now()->startOfMonth()->toDateString(),
        'date_to' => now()->toDateString(),
    ]));

    $pdf->assertOk();
    expect((string) $pdf->headers->get('content-disposition'))->toContain('.pdf');
});

test('admin scan active member qr shows preview before confirm check in', function () {
    $admin = AdminFixtures::admin(['name' => 'Admin Portal']);
    [, $member] = AdminFixtures::member('PG-ADMIN-CHECKIN');

    $package = ServicePackage::create([
        'name' => 'Gym Check In Test',
        'slug' => 'gym-check-in-test',
        'package_kind' => 'membership',
        'type' => 'gym',
        'price' => 250000,
        'duration_days' => 30,
        'is_active' => true,
    ]);

    $membership = Membership::create([
        'member_id' => $member->id,
        'package_id' => $package->id,
        'code' => 'MBR-CHECKIN-0001',
        'start_date' => now()->subDay()->toDateString(),
        'end_date' => now()->addMonth()->toDateString(),
        'price' => 250000,
        'status' => 'active',
    ]);

    $trainer = Trainer::create([
        'name' => 'Coach Adi',
        'specialty' => 'Muaythai',
        'status' => 'active',
    ]);

    $sessionPackage = ServicePackage::create([
        'name' => 'Muaythai Admin Preview 4x',
        'slug' => 'muaythai-admin-preview-4x',
        'package_kind' => 'session',
        'type' => 'muaythai',
        'price' => 400000,
        'session_count' => 4,
        'is_active' => true,
    ]);

    MemberPackageSession::create([
        'member_id' => $member->id,
        'package_id' => $sessionPackage->id,
        'trainer_id' => $trainer->id,
        'code' => 'MPS-ADMIN-PREVIEW-0001',
        'total_sessions' => 4,
        'used_sessions' => 1,
        'remaining_sessions' => 3,
        'price' => 400000,
        'started_at' => now()->subDay()->toDateString(),
        'expired_at' => now()->addMonth()->toDateString(),
        'status' => 'active',
    ]);

    $gymClass = GymClass::create([
        'name' => 'Muaythai Admin Preview Class',
        'slug' => 'muaythai-admin-preview-class',
        'class_type' => 'muaythai',
        'access_type' => 'session_based',
        'required_package_type' => 'muaythai',
        'capacity' => 12,
        'is_active' => true,
    ]);

    $schedule = ClassSchedule::create([
        'gym_class_id' => $gymClass->id,
        'trainer_id' => $trainer->id,
        'day_of_week' => now()->dayOfWeekIso,
        'start_time' => '17:00:00',
        'end_time' => '18:00:00',
        'capacity' => 12,
        'is_active' => true,
    ]);

    ClassEnrollment::create([
        'schedule_id' => $schedule->id,
        'member_id' => $member->id,
        'session_date' => now()->toDateString(),
        'status' => 'confirmed',
    ]);

    $qrToken = QrToken::create([
        'tokenable_type' => Member::class,
        'tokenable_id' => $member->id,
        'token' => str_repeat('a', 64),
        'purpose' => 'member',
        'expires_at' => now()->addMonth(),
    ]);

    $this->actingAs($admin)->post(route('admin.check-in.preview'), [
        'token' => $qrToken->token,
    ])->assertRedirect()
        ->assertSessionHas('check_in_preview');

    expect(GymCheckIn::query()->where('member_id', $member->id)->count())->toBe(0);

    $preview = session('check_in_preview');

    $this->actingAs($admin)->get(route('admin.check-in'))
        ->assertOk()
        ->assertSee('Status Check-in Hari Ini')
        ->assertSee('Gunakan Sesi')
        ->assertSee('Check-in + Gunakan Sesi')
        ->assertSee('Pilih paket sesi yang ingin digunakan')
        ->assertSee('Pilih paket sesi terlebih dahulu sebelum menggunakan sesi.')
        ->assertSee('name="action" x-bind:value="selectedAction"', false)
        ->assertSee("x-on:click=\"selectedAction = 'check_in_membership'\"", false)
        ->assertSee("x-on:click=\"selectedAction = 'use_package_session'\"", false)
        ->assertSee("x-on:click=\"selectedAction = 'check_in_and_use_session'\"", false)
        ->assertSee('Muaythai Admin Preview Class')
        ->assertSee('Muaythai Admin Preview 4x')
        ->assertSee('3/4 sesi')
        ->assertSee('Coach Adi')
        ->assertDontSee('Pakai Sesi')
        ->assertDontSee('Coach Coach Adi');

    $this->actingAs($admin)->post(route('admin.check-in.confirm'), [
        'preview_key' => $preview['preview_key'],
        'action' => 'check_in_membership',
    ])->assertRedirect();

    $this->assertDatabaseHas('gym_check_ins', [
        'member_id' => $member->id,
        'membership_id' => $membership->id,
        'method' => 'qr',
        'scanned_by' => $admin->id,
    ]);
});

test('admin check-in preview disables session actions when member has no active package sessions', function () {
    $admin = AdminFixtures::admin(['name' => 'Admin Portal']);
    [, $member] = AdminFixtures::member('PG-ADMIN-NO-SESSIONS');

    $package = ServicePackage::create([
        'name' => 'Gym No Session Test',
        'slug' => 'gym-no-session-test',
        'package_kind' => 'membership',
        'type' => 'gym',
        'price' => 250000,
        'duration_days' => 30,
        'is_active' => true,
    ]);

    Membership::create([
        'member_id' => $member->id,
        'package_id' => $package->id,
        'code' => 'MBR-NO-SESSIONS-0001',
        'start_date' => now()->subDay()->toDateString(),
        'end_date' => now()->addMonth()->toDateString(),
        'price' => 250000,
        'status' => 'active',
    ]);

    $qrToken = QrToken::create([
        'tokenable_type' => Member::class,
        'tokenable_id' => $member->id,
        'token' => str_repeat('d', 64),
        'purpose' => 'member',
        'expires_at' => now()->addMonth(),
    ]);

    $this->actingAs($admin)->post(route('admin.check-in.preview'), [
        'token' => $qrToken->token,
    ])->assertRedirect()
        ->assertSessionHas('check_in_preview');

    $this->actingAs($admin)->get(route('admin.check-in'))
        ->assertOk()
        ->assertSee('Tidak ada paket sesi aktif yang bisa digunakan.')
        ->assertSee('value="use_package_session" class="admin-button-secondary w-full"', false)
        ->assertSee('value="check_in_and_use_session" class="admin-button-secondary w-full"', false)
        ->assertSee('disabled', false)
        ->assertDontSee('Pakai Sesi');
});

test('admin confirm session action without selecting package session is rejected by backend guard', function () {
    $admin = AdminFixtures::admin(['name' => 'Admin Portal']);
    [, $member] = AdminFixtures::member('PG-ADMIN-SESSION-GUARD');

    $membershipPackage = ServicePackage::create([
        'name' => 'Gym Session Guard Membership',
        'slug' => 'gym-session-guard-membership',
        'package_kind' => 'membership',
        'type' => 'gym',
        'price' => 250000,
        'duration_days' => 30,
        'is_active' => true,
    ]);

    Membership::create([
        'member_id' => $member->id,
        'package_id' => $membershipPackage->id,
        'code' => 'MBR-SESSION-GUARD-0001',
        'start_date' => now()->subDay()->toDateString(),
        'end_date' => now()->addMonth()->toDateString(),
        'price' => 250000,
        'status' => 'active',
    ]);

    $qrToken = QrToken::create([
        'tokenable_type' => Member::class,
        'tokenable_id' => $member->id,
        'token' => str_repeat('e', 64),
        'purpose' => 'member',
        'expires_at' => now()->addMonth(),
    ]);

    $this->actingAs($admin)->post(route('admin.check-in.preview'), [
        'token' => $qrToken->token,
    ])->assertRedirect()
        ->assertSessionHas('check_in_preview');

    $preview = session('check_in_preview');

    $this->actingAs($admin)->post(route('admin.check-in.confirm'), [
        'preview_key' => $preview['preview_key'],
        'action' => 'use_package_session',
    ])->assertSessionHasErrors(['member_package_session_id']);
});

test('admin can explicitly use one package session after qr preview', function () {
    $admin = AdminFixtures::admin(['name' => 'Admin Portal']);
    [, $member] = AdminFixtures::member('PG-ADMIN-SESSION-USAGE');

    $membershipPackage = ServicePackage::create([
        'name' => 'Gym Session Preview Membership',
        'slug' => 'gym-session-preview-membership',
        'package_kind' => 'membership',
        'type' => 'gym',
        'price' => 250000,
        'duration_days' => 30,
        'is_active' => true,
    ]);

    Membership::create([
        'member_id' => $member->id,
        'package_id' => $membershipPackage->id,
        'code' => 'MBR-SESSION-USAGE-0001',
        'start_date' => now()->subDay()->toDateString(),
        'end_date' => now()->addMonth()->toDateString(),
        'price' => 250000,
        'status' => 'active',
    ]);

    $sessionPackage = ServicePackage::create([
        'name' => 'PT Session Usage Test',
        'slug' => 'pt-session-usage-test',
        'package_kind' => 'session',
        'type' => 'personal_training',
        'price' => 400000,
        'session_count' => 4,
        'is_active' => true,
    ]);

    $packageSession = MemberPackageSession::create([
        'member_id' => $member->id,
        'package_id' => $sessionPackage->id,
        'code' => 'MPS-SESSION-USAGE-0001',
        'total_sessions' => 4,
        'used_sessions' => 0,
        'remaining_sessions' => 4,
        'price' => 400000,
        'started_at' => now()->subDay()->toDateString(),
        'expired_at' => now()->addMonth()->toDateString(),
        'status' => 'active',
    ]);

    $qrToken = QrToken::create([
        'tokenable_type' => Member::class,
        'tokenable_id' => $member->id,
        'token' => str_repeat('b', 64),
        'purpose' => 'member',
        'expires_at' => now()->addMonth(),
    ]);

    $this->actingAs($admin)->post(route('admin.check-in.preview'), [
        'token' => $qrToken->token,
    ])->assertRedirect()
        ->assertSessionHas('check_in_preview');

    $preview = session('check_in_preview');

    $this->actingAs($admin)->post(route('admin.check-in.confirm'), [
        'preview_key' => $preview['preview_key'],
        'action' => 'use_package_session',
        'member_package_session_id' => $packageSession->id,
    ])->assertRedirect();

    expect($packageSession->refresh())
        ->used_sessions->toBe(1)
        ->remaining_sessions->toBe(3)
        ->and(MemberPackageSessionUsage::query()->where('member_package_session_id', $packageSession->id)->count())->toBe(1)
        ->and(GymCheckIn::query()->where('member_id', $member->id)->count())->toBe(0);
});

test('admin can check in and use one package session after qr preview', function () {
    $admin = AdminFixtures::admin(['name' => 'Admin Portal']);
    [, $member] = AdminFixtures::member('PG-ADMIN-CHECKIN-SESSION');

    $membershipPackage = ServicePackage::create([
        'name' => 'Gym Check In Session Membership',
        'slug' => 'gym-check-in-session-membership',
        'package_kind' => 'membership',
        'type' => 'gym',
        'price' => 250000,
        'duration_days' => 30,
        'is_active' => true,
    ]);

    Membership::create([
        'member_id' => $member->id,
        'package_id' => $membershipPackage->id,
        'code' => 'MBR-CHECKIN-SESSION-0001',
        'start_date' => now()->subDay()->toDateString(),
        'end_date' => now()->addMonth()->toDateString(),
        'price' => 250000,
        'status' => 'active',
    ]);

    $sessionPackage = ServicePackage::create([
        'name' => 'Muaythai Check In Session Test',
        'slug' => 'muaythai-check-in-session-test',
        'package_kind' => 'session',
        'type' => 'muaythai',
        'price' => 400000,
        'session_count' => 4,
        'is_active' => true,
    ]);

    $packageSession = MemberPackageSession::create([
        'member_id' => $member->id,
        'package_id' => $sessionPackage->id,
        'code' => 'MPS-CHECKIN-SESSION-0001',
        'total_sessions' => 4,
        'used_sessions' => 0,
        'remaining_sessions' => 4,
        'price' => 400000,
        'started_at' => now()->subDay()->toDateString(),
        'expired_at' => now()->addMonth()->toDateString(),
        'status' => 'active',
    ]);

    $gymClass = GymClass::create([
        'name' => 'Muaythai Check In Session Class',
        'slug' => 'muaythai-check-in-session-class',
        'class_type' => 'muaythai',
        'access_type' => 'session_based',
        'required_package_type' => 'muaythai',
        'capacity' => 12,
        'is_active' => true,
    ]);

    $schedule = ClassSchedule::create([
        'gym_class_id' => $gymClass->id,
        'day_of_week' => now()->dayOfWeekIso,
        'start_time' => '17:00:00',
        'end_time' => '18:00:00',
        'capacity' => 12,
        'is_active' => true,
    ]);

    $enrollment = ClassEnrollment::create([
        'schedule_id' => $schedule->id,
        'member_id' => $member->id,
        'session_date' => now()->toDateString(),
        'status' => 'confirmed',
    ]);

    $qrToken = QrToken::create([
        'tokenable_type' => Member::class,
        'tokenable_id' => $member->id,
        'token' => str_repeat('c', 64),
        'purpose' => 'member',
        'expires_at' => now()->addMonth(),
    ]);

    $this->actingAs($admin)->post(route('admin.check-in.preview'), [
        'token' => $qrToken->token,
    ])->assertRedirect()
        ->assertSessionHas('check_in_preview');

    $preview = session('check_in_preview');

    $this->actingAs($admin)->post(route('admin.check-in.confirm'), [
        'preview_key' => $preview['preview_key'],
        'action' => 'check_in_and_use_session',
        'member_package_session_id' => $packageSession->id,
        'class_enrollment_id' => $enrollment->id,
    ])->assertRedirect();

    $checkIn = GymCheckIn::query()->where('member_id', $member->id)->firstOrFail();
    $usage = MemberPackageSessionUsage::query()->where('member_package_session_id', $packageSession->id)->firstOrFail();

    expect($packageSession->refresh())
        ->used_sessions->toBe(1)
        ->remaining_sessions->toBe(3)
        ->and($checkIn->method)->toBe('qr')
        ->and($usage->gym_check_in_id)->toBe($checkIn->id)
        ->and($usage->class_enrollment_id)->toBe($enrollment->id)
        ->and($enrollment->refresh()->status)->toBe('attended');
});

test('admin can toggle product active status', function () {
    $admin = AdminFixtures::admin(['name' => 'Admin Portal']);

    $product = Product::create([
        'name' => 'Toggle Product Test',
        'slug' => 'toggle-product-test',
        'price' => 100000,
        'stock' => 10,
        'is_active' => true,
    ]);

    $this->actingAs($admin)->patch(route('admin.resources.toggle', ['resource' => 'products', 'id' => $product->id]))->assertRedirect();

    expect($product->refresh()->is_active)->toBeFalse();
});

test('admin can create and update product resource', function () {
    $admin = AdminFixtures::admin(['name' => 'Admin Portal']);

    $this->actingAs($admin)->get(route('admin.resources.create', 'products'))
        ->assertOk()
        ->assertSee('Tambah Produk')
        ->assertSee('Nama Produk');

    $this->actingAs($admin)->post(route('admin.resources.store', 'products'), [
        'name' => 'Admin CRUD Product',
        'price' => 125000,
        'stock' => 7,
        'description' => 'Produk dibuat dari admin custom Blade.',
        'is_active' => 1,
    ])->assertRedirect(route('admin.products'));

    $product = Product::query()->where('slug', 'admin-crud-product')->firstOrFail();
    expect($product->price)->toBe('125000.00')
        ->and($product->stock)->toBe(7)
        ->and($product->is_active)->toBeTrue();

    $this->actingAs($admin)->patch(route('admin.resources.update', ['resource' => 'products', 'id' => $product->id]), [
        'name' => 'Admin CRUD Product Updated',
        'slug' => 'admin-crud-product',
        'price' => 150000,
        'stock' => 9,
        'is_active' => 1,
    ])->assertRedirect(route('admin.products'));

    expect($product->refresh()->name)->toBe('Admin CRUD Product Updated')
        ->and($product->price)->toBe('150000.00')
        ->and($product->stock)->toBe(9);
});

test('admin product table uses server side pagination', function () {
    $admin = AdminFixtures::admin(['name' => 'Admin Portal']);

    foreach (range(1, 14) as $index) {
        Product::create([
            'name' => 'Paged Product '.str_pad((string) $index, 2, '0', STR_PAD_LEFT),
            'slug' => 'paged-product-'.str_pad((string) $index, 2, '0', STR_PAD_LEFT),
            'price' => 100000 + $index,
            'stock' => $index,
            'is_active' => true,
        ]);
    }

    $this->actingAs($admin)->get(route('admin.products'))
        ->assertOk()
        ->assertSee('Menampilkan 1-12 dari 14 data')
        ->assertSee('Berikutnya')
        ->assertSee('Paged Product 01')
        ->assertDontSee('Paged Product 13');

    $this->actingAs($admin)->get(route('admin.products', ['page' => 2]))
        ->assertOk()
        ->assertSee('Menampilkan 13-14 dari 14 data')
        ->assertSee('Paged Product 13')
        ->assertDontSee('Paged Product 01');
});

test('admin product search is applied before pagination and preserves query string', function () {
    $admin = AdminFixtures::admin(['name' => 'Admin Portal']);

    Product::create([
        'name' => 'Outside Search Product',
        'slug' => 'outside-search-product',
        'price' => 90000,
        'stock' => 5,
        'is_active' => true,
    ]);

    foreach (range(1, 13) as $index) {
        Product::create([
            'name' => 'Needle Product '.str_pad((string) $index, 2, '0', STR_PAD_LEFT),
            'slug' => 'needle-product-'.str_pad((string) $index, 2, '0', STR_PAD_LEFT),
            'price' => 125000 + $index,
            'stock' => $index,
            'is_active' => true,
        ]);
    }

    $this->actingAs($admin)->get(route('admin.products', ['q' => 'Needle']))
        ->assertOk()
        ->assertSee('Menampilkan 1-12 dari 13 data')
        ->assertSee('value="Needle"', false)
        ->assertSee('q=Needle', false)
        ->assertSee('Needle Product 01')
        ->assertDontSee('Outside Search Product');

    $this->actingAs($admin)->get(route('admin.products', ['q' => 'Needle', 'page' => 2]))
        ->assertOk()
        ->assertSee('Needle Product 13')
        ->assertDontSee('Outside Search Product');
});

test('admin product status filter is server side', function () {
    $admin = AdminFixtures::admin(['name' => 'Admin Portal']);

    Product::create([
        'name' => 'Visible Product Filter',
        'slug' => 'visible-product-filter',
        'price' => 100000,
        'stock' => 4,
        'is_active' => true,
    ]);

    Product::create([
        'name' => 'Hidden Product Filter',
        'slug' => 'hidden-product-filter',
        'price' => 100000,
        'stock' => 4,
        'is_active' => false,
    ]);

    $this->actingAs($admin)->get(route('admin.products', ['status' => 'inactive']))
        ->assertOk()
        ->assertSee('Hidden Product Filter')
        ->assertSee('Nonaktif')
        ->assertDontSee('Visible Product Filter');
});
