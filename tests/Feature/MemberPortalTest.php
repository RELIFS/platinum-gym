<?php

use App\Models\ClassEnrollment;
use App\Models\ClassSchedule;
use App\Models\GymClass;
use App\Models\Member;
use App\Models\MemberPackageSession;
use App\Models\Membership;
use App\Models\Package as ServicePackage;
use App\Models\Payment;
use App\Models\QrToken;
use App\Models\User;
use Carbon\CarbonImmutable;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Support\Facades\Http;
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
]);

test('member ai assistant page route is removed', function () {
    [$user] = createPortalMember('PG-PORTAL-NO-AI-PAGE');

    $this->actingAs($user)->get('/member/ai-assistant')->assertNotFound();
});

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
]);

test('member can update profile from member portal', function () {
    [$user, $member] = createPortalMember('PG-PORTAL-UPDATE');

    $this->actingAs($user)->patch(route('member.profile.update'), [
        'name' => 'Andi Updated',
        'email' => 'andi.updated@example.com',
        'phone' => '+62 812-3456-7899',
        'gender' => 'female',
        'birth_date' => '1999-02-03',
        'address' => 'Jl. Khatib Sulaiman No. 10',
        'emergency_contact' => '6281299990001',
        'is_student' => '1',
        'student_id_number' => 'MHS-12345',
        'height_cm' => 165,
        'weight_kg' => 58.5,
    ])->assertRedirect(route('verification.notice'));

    expect($user->refresh())
        ->name->toBe('Andi Updated')
        ->email->toBe('andi.updated@example.com')
        ->phone->toBe('081234567899')
        ->email_verified_at->toBeNull()
        ->and($member->refresh())
        ->gender->toBe('female')
        ->birth_date->toDateString()->toBe('1999-02-03')
        ->address->toBe('Jl. Khatib Sulaiman No. 10')
        ->emergency_contact->toBe('081299990001')
        ->is_student->toBeTrue()
        ->student_id_number->toBe('MHS-12345')
        ->height_cm->toBe(165)
        ->weight_kg->toBe('58.50');
});

test('member profile update rejects duplicate whatsapp number', function () {
    [$user] = createPortalMember('PG-PORTAL-DUPLICATE-PHONE');
    User::factory()->create(['phone' => '081277778888']);

    $this->actingAs($user)->from(route('member.profile'))->patch(route('member.profile.update'), [
        'name' => 'Andi Portal',
        'email' => 'andi.portal@example.com',
        'phone' => '081277778888',
        'gender' => 'male',
        'birth_date' => '2000-01-01',
    ])->assertRedirect(route('member.profile'))
        ->assertSessionHasErrors('phone');
});

test('dashboard renders real member data and empty operational states', function () {
    [$user] = createPortalMember('PG-PORTAL-REAL');

    $this->actingAs($user)->get('/member/dashboard')
        ->assertOk()
        ->assertSee('Andi Portal')
        ->assertSee('PG-PORTAL-REAL')
        ->assertSee('Belum ada membership aktif')
        ->assertSee('Belum ada jadwal terdaftar')
        ->assertSee('Belum ada transaksi')
        ->assertSee('Belum diterbitkan')
        ->assertSee('Gymmi')
        ->assertSee('role="log"', false)
        ->assertSee('aria-label="Percakapan Gymmi"', false)
        ->assertSee('name="gymmi_member_message"', false)
        ->assertSee('autocomplete="off"', false)
        ->assertSee('spellcheck="true"', false)
        ->assertSee('Ketik pertanyaan untuk Gymmi', false)
        ->assertDontSee('AI Assistant')
        ->assertDontSee('Akun dan Bantuan');
});

test('member portal hides exhausted and expired package sessions', function () {
    [$user, $member] = createPortalMember('PG-PORTAL-SESSIONS');

    $package = ServicePackage::create([
        'name' => 'PT Aktif Portal',
        'slug' => 'pt-aktif-portal',
        'package_kind' => 'session',
        'type' => 'personal_training',
        'price' => 400000,
        'session_count' => 4,
        'is_active' => true,
    ]);

    $emptyPackage = ServicePackage::create([
        'name' => 'PT Habis Portal',
        'slug' => 'pt-habis-portal',
        'package_kind' => 'session',
        'type' => 'personal_training',
        'price' => 400000,
        'session_count' => 4,
        'is_active' => true,
    ]);

    $expiredPackage = ServicePackage::create([
        'name' => 'PT Expired Portal',
        'slug' => 'pt-expired-portal',
        'package_kind' => 'session',
        'type' => 'personal_training',
        'price' => 400000,
        'session_count' => 4,
        'is_active' => true,
    ]);

    MemberPackageSession::create([
        'member_id' => $member->id,
        'package_id' => $package->id,
        'code' => 'MPS-ACTIVE-0001',
        'total_sessions' => 4,
        'used_sessions' => 1,
        'remaining_sessions' => 3,
        'price' => 400000,
        'started_at' => now()->subDay()->toDateString(),
        'expired_at' => now()->addMonth()->toDateString(),
        'status' => 'active',
    ]);

    MemberPackageSession::create([
        'member_id' => $member->id,
        'package_id' => $emptyPackage->id,
        'code' => 'MPS-EMPTY-0001',
        'total_sessions' => 4,
        'used_sessions' => 4,
        'remaining_sessions' => 0,
        'price' => 400000,
        'started_at' => now()->subMonth()->toDateString(),
        'expired_at' => now()->addMonth()->toDateString(),
        'status' => 'active',
    ]);

    MemberPackageSession::create([
        'member_id' => $member->id,
        'package_id' => $expiredPackage->id,
        'code' => 'MPS-EXPIRED-0001',
        'total_sessions' => 4,
        'used_sessions' => 0,
        'remaining_sessions' => 4,
        'price' => 400000,
        'started_at' => now()->subMonths(2)->toDateString(),
        'expired_at' => now()->subDay()->toDateString(),
        'status' => 'active',
    ]);

    $this->actingAs($user)->get('/member/membership')
        ->assertOk()
        ->assertSee('PT Aktif Portal')
        ->assertSee('3 dari 4 sesi tersisa')
        ->assertDontSee('MPS-EMPTY-0001')
        ->assertDontSee('MPS-EXPIRED-0001')
        ->assertDontSee('PT Habis Portal')
        ->assertDontSee('PT Expired Portal');
});

test('expired qr token is not shown as active', function () {
    [$user, $member] = createPortalMember('PG-PORTAL-QR-EXPIRED');

    QrToken::create([
        'tokenable_type' => Member::class,
        'tokenable_id' => $member->id,
        'token' => Str::random(64),
        'purpose' => 'member',
        'expires_at' => now()->subMinute(),
    ]);

    $this->actingAs($user)->get('/member/dashboard')
        ->assertOk()
        ->assertSee('Kedaluwarsa')
        ->assertDontSee('Status QR aktif');
});

test('revoked qr token is not shown as active', function () {
    [$user, $member] = createPortalMember('PG-PORTAL-QR-REVOKED');

    QrToken::create([
        'tokenable_type' => Member::class,
        'tokenable_id' => $member->id,
        'token' => Str::random(64),
        'purpose' => 'member',
        'is_revoked' => true,
    ]);

    $this->actingAs($user)->get('/member/qr')
        ->assertOk()
        ->assertSee('Dicabut')
        ->assertDontSee('Aktif</dd>', false);
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
        ->assertSee('QR siap dipantau')
        ->assertDontSee($token);
});

test('member can view transaction detail and continue midtrans payment', function () {
    [$user, $member] = createPortalMember('PG-PORTAL-PAYMENT-DETAIL');

    $package = ServicePackage::create([
        'name' => 'Gym Detail Payment Test',
        'slug' => 'gym-detail-payment-test',
        'package_kind' => 'membership',
        'type' => 'gym',
        'price' => 199000,
        'duration_days' => 30,
        'is_active' => true,
    ]);

    $membership = Membership::create([
        'member_id' => $member->id,
        'package_id' => $package->id,
        'code' => 'MBR-PAYMENT-DETAIL-0001',
        'start_date' => now()->toDateString(),
        'end_date' => now()->addMonth()->toDateString(),
        'price' => 199000,
        'status' => 'pending_payment',
    ]);

    $payment = Payment::create([
        'payment_code' => 'PAY-DETAIL-0001',
        'member_id' => $member->id,
        'payable_type' => Membership::class,
        'payable_id' => $membership->id,
        'method' => 'midtrans',
        'amount' => 199000,
        'status' => 'waiting_payment',
        'midtrans_order_id' => 'PAY-DETAIL-0001-120000',
        'midtrans_snap_token' => 'snap-token-detail-test',
        'midtrans_redirect_url' => 'https://sandbox.midtrans.test/pay',
    ]);

    $this->actingAs($user)->get(route('member.transactions.show', $payment))
        ->assertOk()
        ->assertSee('Detail Transaksi')
        ->assertSee('PAY-DETAIL-0001')
        ->assertSee('Bayar via Midtrans');

    $this->actingAs($user)->post(route('member.transactions.pay', $payment))
        ->assertRedirect('https://sandbox.midtrans.test/pay');
});

test('member chatbot config includes safe internal fallbacks for extra topics', function () {
    [$user] = createPortalMember('PG-PORTAL-CHATBOT');

    $this->actingAs($user)->get('/member/dashboard')
        ->assertOk()
        ->assertSee('Informasi personal trainer dan layanan latihan dapat dilihat di katalog layanan Platinum Gym Padang.')
        ->assertSee('Alamat, maps, dan kontak Platinum Gym Padang tersedia di halaman lokasi.')
        ->assertSee('Promo aktif ditampilkan di website jika sedang tersedia.')
        ->assertSee('Buka Jadwal Kelas')
        ->assertDontSee('flow booking');
});

test('member can checkout membership with midtrans sandbox token', function () {
    config(['services.midtrans.server_key' => 'server-test-key']);

    Http::fake([
        'app.sandbox.midtrans.com/*' => Http::response([
            'token' => 'snap-token-test',
            'redirect_url' => 'https://sandbox.midtrans.test/pay',
        ], 201),
    ]);

    [$user, $member] = createPortalMember('PG-PORTAL-CHECKOUT');

    $package = ServicePackage::create([
        'name' => 'Gym Checkout Test',
        'slug' => 'gym-checkout-test',
        'package_kind' => 'membership',
        'type' => 'gym',
        'price' => 250000,
        'duration_days' => 30,
        'is_active' => true,
    ]);

    $response = $this->actingAs($user)->post(route('member.membership.checkout', $package));

    $payment = Payment::query()->where('member_id', $member->id)->latest()->first();

    expect($payment)->not->toBeNull();

    $response->assertRedirect(route('member.transactions.show', $payment));
    expect($payment->status)->toBe('waiting_payment')
        ->and($payment->midtrans_snap_token)->toBe('snap-token-test')
        ->and($payment->invoice)->not->toBeNull();
});

test('midtrans webhook activates membership and issues qr token', function () {
    config(['services.midtrans.server_key' => 'server-test-key']);

    [, $member] = createPortalMember('PG-PORTAL-WEBHOOK');

    $package = ServicePackage::create([
        'name' => 'Gym Webhook Test',
        'slug' => 'gym-webhook-test',
        'package_kind' => 'membership',
        'type' => 'gym',
        'price' => 250000,
        'duration_days' => 30,
        'is_active' => true,
    ]);

    $membership = Membership::create([
        'member_id' => $member->id,
        'package_id' => $package->id,
        'code' => 'MBR-WEBHOOK-0001',
        'start_date' => now()->toDateString(),
        'end_date' => now()->addMonth()->toDateString(),
        'price' => 250000,
        'status' => 'pending_payment',
    ]);

    $payment = Payment::create([
        'payment_code' => 'PAY-WEBHOOK-0001',
        'member_id' => $member->id,
        'payable_type' => Membership::class,
        'payable_id' => $membership->id,
        'method' => 'midtrans',
        'amount' => 250000,
        'status' => 'waiting_payment',
        'midtrans_order_id' => 'PAY-WEBHOOK-0001-120000',
    ]);

    $payload = [
        'order_id' => $payment->midtrans_order_id,
        'status_code' => '200',
        'gross_amount' => '250000.00',
        'transaction_status' => 'settlement',
        'transaction_id' => 'midtrans-transaction-test',
        'payment_type' => 'bank_transfer',
    ];
    $payload['signature_key'] = hash('sha512', $payload['order_id'].$payload['status_code'].$payload['gross_amount'].'server-test-key');

    $this->postJson(route('webhooks.midtrans'), $payload)->assertOk();

    expect($payment->refresh()->status)->toBe('paid')
        ->and($membership->refresh()->status)->toBe('active')
        ->and(QrToken::query()->where('tokenable_id', $member->id)->where('purpose', 'member')->exists())->toBeTrue();
});

test('member can book included class with active matching membership', function () {
    [$user, $member] = createPortalMember('PG-PORTAL-BOOKING');

    $package = ServicePackage::create([
        'name' => 'Senam Booking Test',
        'slug' => 'senam-booking-test',
        'package_kind' => 'membership',
        'type' => 'senam',
        'price' => 249000,
        'duration_days' => 30,
        'is_active' => true,
    ]);

    Membership::create([
        'member_id' => $member->id,
        'package_id' => $package->id,
        'code' => 'MBR-BOOKING-0001',
        'start_date' => now()->subDay()->toDateString(),
        'end_date' => now()->addMonth()->toDateString(),
        'price' => 249000,
        'status' => 'active',
    ]);

    $gymClass = GymClass::create([
        'name' => 'Aerobic Booking Test',
        'slug' => 'aerobic-booking-test',
        'class_type' => 'senam',
        'access_type' => 'included',
        'required_package_type' => 'senam',
        'capacity' => 25,
        'is_active' => true,
    ]);

    $sessionDate = CarbonImmutable::today()->next(CarbonImmutable::MONDAY);

    $schedule = ClassSchedule::create([
        'gym_class_id' => $gymClass->id,
        'day_of_week' => $sessionDate->dayOfWeekIso,
        'start_time' => '17:00:00',
        'end_time' => '18:00:00',
        'capacity' => 25,
        'is_active' => true,
    ]);

    $this->actingAs($user)->post(route('member.booking.store', $schedule), [
        'session_date' => $sessionDate->toDateString(),
    ])->assertRedirect(route('member.bookings'));

    $this->assertDatabaseHas('class_enrollments', [
        'schedule_id' => $schedule->id,
        'member_id' => $member->id,
        'session_date' => $sessionDate->toDateString(),
        'status' => 'booked',
    ]);
});

test('member can cancel own class booking', function () {
    [$user, $member] = createPortalMember('PG-PORTAL-CANCEL-BOOKING');

    $gymClass = GymClass::create([
        'name' => 'Cancel Booking Test',
        'slug' => 'cancel-booking-test',
        'class_type' => 'senam',
        'access_type' => 'included',
        'required_package_type' => 'senam',
        'capacity' => 25,
        'is_active' => true,
    ]);

    $sessionDate = CarbonImmutable::today()->next(CarbonImmutable::MONDAY);

    $schedule = ClassSchedule::create([
        'gym_class_id' => $gymClass->id,
        'day_of_week' => $sessionDate->dayOfWeekIso,
        'start_time' => '17:00:00',
        'end_time' => '18:00:00',
        'capacity' => 25,
        'is_active' => true,
    ]);

    $enrollment = ClassEnrollment::create([
        'schedule_id' => $schedule->id,
        'member_id' => $member->id,
        'session_date' => $sessionDate->toDateString(),
        'status' => 'booked',
    ]);

    $this->actingAs($user)->delete(route('member.bookings.destroy', $enrollment))
        ->assertRedirect(route('member.bookings'));

    expect($enrollment->refresh()->status)->toBe('cancelled');
});
