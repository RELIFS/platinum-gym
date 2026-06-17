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
use Illuminate\Notifications\DatabaseNotification;
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
        ->assertDontSee('Member Area')
        ->assertDontSee('Akun Login')
        ->assertDontSee('>Website<', false)
        ->assertDontSee('Midtrans Sandbox');
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

test('member transactions use server side pagination search and status filter', function () {
    [$user, $member] = createPortalMember('PG-PORTAL-PAGINATED-PAYMENTS');

    $otherUser = User::factory()->create(['email' => 'other.payment@example.com', 'phone' => '081299999901']);
    $otherUser->assignRole('member');
    $otherMember = Member::create([
        'user_id' => $otherUser->id,
        'member_code' => 'PG-PORTAL-OTHER-PAYMENTS',
        'gender' => 'male',
        'birth_date' => '1999-01-01',
        'joined_at' => now()->subMonth()->toDateString(),
        'status' => 'active',
    ]);

    $package = ServicePackage::create([
        'name' => 'Membership Payment Pagination',
        'slug' => 'membership-payment-pagination',
        'package_kind' => 'membership',
        'type' => 'gym',
        'price' => 250000,
        'duration_days' => 30,
        'is_active' => true,
    ]);

    $membership = Membership::create([
        'member_id' => $member->id,
        'package_id' => $package->id,
        'code' => 'MBR-PAGINATED-PAYMENTS',
        'start_date' => now()->toDateString(),
        'end_date' => now()->addMonth()->toDateString(),
        'price' => 250000,
        'status' => 'active',
    ]);

    $otherMembership = Membership::create([
        'member_id' => $otherMember->id,
        'package_id' => $package->id,
        'code' => 'MBR-OTHER-PAYMENTS',
        'start_date' => now()->toDateString(),
        'end_date' => now()->addMonth()->toDateString(),
        'price' => 250000,
        'status' => 'active',
    ]);

    foreach (range(1, 12) as $index) {
        $payment = Payment::create([
            'payment_code' => 'PAY-PAGE-'.str_pad((string) $index, 2, '0', STR_PAD_LEFT),
            'member_id' => $member->id,
            'payable_type' => Membership::class,
            'payable_id' => $membership->id,
            'method' => 'midtrans',
            'amount' => 100000 + $index,
            'status' => 'paid',
        ]);
        $payment->forceFill(['created_at' => now()->subMinutes($index), 'updated_at' => now()->subMinutes($index)])->save();
    }

    $waitingPayment = Payment::create([
        'payment_code' => 'PAY-WAIT-01',
        'member_id' => $member->id,
        'payable_type' => Membership::class,
        'payable_id' => $membership->id,
        'method' => 'midtrans',
        'amount' => 150000,
        'status' => 'waiting_payment',
        'midtrans_redirect_url' => 'https://sandbox.midtrans.test/pay-wait',
    ]);
    $waitingPayment->forceFill(['created_at' => now(), 'updated_at' => now()])->save();

    Payment::create([
        'payment_code' => 'PAY-OTHER-PRIVATE',
        'member_id' => $otherMember->id,
        'payable_type' => Membership::class,
        'payable_id' => $otherMembership->id,
        'method' => 'midtrans',
        'amount' => 999000,
        'status' => 'paid',
    ]);

    $this->actingAs($user)->get('/member/transaksi')
        ->assertOk()
        ->assertSee('Menampilkan 1-8 dari 13 data')
        ->assertSee('PAY-WAIT-01')
        ->assertSee('PAY-PAGE-07')
        ->assertDontSee('PAY-PAGE-09')
        ->assertDontSee('PAY-OTHER-PRIVATE');

    $this->actingAs($user)->get('/member/transaksi?page=2')
        ->assertOk()
        ->assertSee('Menampilkan 9-13 dari 13 data')
        ->assertSee('PAY-PAGE-09')
        ->assertDontSee('PAY-WAIT-01');

    $this->actingAs($user)->get('/member/transaksi?status=paid')
        ->assertOk()
        ->assertSee('Menampilkan 1-8 dari 12 data')
        ->assertSee('status=paid', false)
        ->assertDontSee('PAY-WAIT-01');

    $this->actingAs($user)->get('/member/transaksi?q=PAY-PAGE-12')
        ->assertOk()
        ->assertSee('Menampilkan 1-1 dari 1 data')
        ->assertSee('PAY-PAGE-12')
        ->assertDontSee('PAY-PAGE-01');
});

test('member package catalog uses server side pagination and filters', function () {
    [$user] = createPortalMember('PG-PORTAL-PAGINATED-PACKAGES');

    foreach (range(1, 7) as $index) {
        ServicePackage::create([
            'name' => 'Paket Member '.str_pad((string) $index, 2, '0', STR_PAD_LEFT),
            'slug' => 'paket-member-page-'.$index,
            'package_kind' => 'membership',
            'type' => 'gym',
            'price' => 200000 + $index,
            'duration_days' => 30,
            'is_active' => true,
        ]);
    }

    ServicePackage::create([
        'name' => 'PT Filter Member',
        'slug' => 'pt-filter-member',
        'package_kind' => 'personal_trainer',
        'type' => 'pt',
        'price' => 500000,
        'session_count' => 5,
        'is_active' => true,
    ]);

    $this->actingAs($user)->get('/member/membership')
        ->assertOk()
        ->assertSee('Menampilkan 1-6 dari 8 data')
        ->assertSee('Paket Member 01')
        ->assertDontSee('Paket Member 07');

    $this->actingAs($user)->get('/member/membership?page=2')
        ->assertOk()
        ->assertSee('Menampilkan 7-8 dari 8 data')
        ->assertSee('Paket Member 07')
        ->assertSee('PT Filter Member');

    $this->actingAs($user)->get('/member/membership?kind=membership')
        ->assertOk()
        ->assertSee('Menampilkan 1-6 dari 7 data')
        ->assertSee('kind=membership', false)
        ->assertDontSee('PT Filter Member');

    $this->actingAs($user)->get('/member/membership?q=Paket%20Member%2007')
        ->assertOk()
        ->assertSee('Menampilkan 1-1 dari 1 data')
        ->assertSee('Paket Member 07')
        ->assertSee('Checkout Membership');
});

test('member class schedule uses server side pagination and filters', function () {
    [$user] = createPortalMember('PG-PORTAL-PAGINATED-SCHEDULES');

    foreach (range(1, 10) as $index) {
        $accessType = $index >= 9 ? 'paid' : 'included';
        $gymClass = GymClass::create([
            'name' => 'Jadwal Member '.str_pad((string) $index, 2, '0', STR_PAD_LEFT),
            'slug' => 'jadwal-member-page-'.$index,
            'class_type' => 'senam',
            'access_type' => $accessType,
            'required_package_type' => 'senam',
            'capacity' => 20,
            'member_price' => $accessType === 'paid' ? 50000 : 0,
            'is_active' => true,
        ]);

        ClassSchedule::create([
            'gym_class_id' => $gymClass->id,
            'day_of_week' => 1,
            'start_time' => '08:'.str_pad((string) $index, 2, '0', STR_PAD_LEFT).':00',
            'end_time' => '09:'.str_pad((string) $index, 2, '0', STR_PAD_LEFT).':00',
            'capacity' => 20,
            'is_active' => true,
        ]);
    }

    $this->actingAs($user)->get('/member/booking-kelas')
        ->assertOk()
        ->assertSee('Menampilkan 1-9 dari 10 data')
        ->assertSee('Jadwal Member 01')
        ->assertDontSee('Jadwal Member 10');

    $this->actingAs($user)->get('/member/booking-kelas?page=2')
        ->assertOk()
        ->assertSee('Menampilkan 10-10 dari 10 data')
        ->assertSee('Jadwal Member 10');

    $this->actingAs($user)->get('/member/booking-kelas?access=paid')
        ->assertOk()
        ->assertSee('Menampilkan 1-2 dari 2 data')
        ->assertSee('Jadwal Member 09')
        ->assertSee('Jadwal Member 10')
        ->assertDontSee('Jadwal Member 01');

    $this->actingAs($user)->get('/member/booking-kelas?q=Jadwal%20Member%2010')
        ->assertOk()
        ->assertSee('Menampilkan 1-1 dari 1 data')
        ->assertSee('Jadwal Member 10')
        ->assertSee('Booking Kelas');
});

test('member booking history uses server side pagination and own data filters', function () {
    [$user, $member] = createPortalMember('PG-PORTAL-PAGINATED-BOOKINGS');

    $gymClass = GymClass::create([
        'name' => 'History Class Member',
        'slug' => 'history-class-member',
        'class_type' => 'senam',
        'access_type' => 'included',
        'required_package_type' => 'senam',
        'capacity' => 20,
        'is_active' => true,
    ]);

    $schedule = ClassSchedule::create([
        'gym_class_id' => $gymClass->id,
        'day_of_week' => 1,
        'start_time' => '17:00:00',
        'end_time' => '18:00:00',
        'capacity' => 20,
        'is_active' => true,
    ]);

    foreach (range(1, 9) as $index) {
        ClassEnrollment::create([
            'schedule_id' => $schedule->id,
            'member_id' => $member->id,
            'session_date' => now()->addDays($index)->toDateString(),
            'status' => $index === 9 ? 'cancelled' : 'booked',
        ]);
    }

    $otherUser = User::factory()->create(['email' => 'other.booking@example.com', 'phone' => '081299999902']);
    $otherUser->assignRole('member');
    $otherMember = Member::create([
        'user_id' => $otherUser->id,
        'member_code' => 'PG-PORTAL-OTHER-BOOKING',
        'gender' => 'female',
        'birth_date' => '1998-01-01',
        'joined_at' => now()->subMonth()->toDateString(),
        'status' => 'active',
    ]);
    ClassEnrollment::create([
        'schedule_id' => $schedule->id,
        'member_id' => $otherMember->id,
        'session_date' => now()->addDays(20)->toDateString(),
        'status' => 'booked',
    ]);

    $this->actingAs($user)->get('/member/riwayat-booking')
        ->assertOk()
        ->assertSee('Menampilkan 1-8 dari 9 data')
        ->assertSee('History Class Member')
        ->assertDontSee('PG-PORTAL-OTHER-BOOKING');

    $this->actingAs($user)->get('/member/riwayat-booking?status=cancelled')
        ->assertOk()
        ->assertSee('Menampilkan 1-1 dari 1 data')
        ->assertSee('Dibatalkan');

    $this->actingAs($user)->get('/member/riwayat-booking?q=History%20Class')
        ->assertOk()
        ->assertSee('Menampilkan 1-8 dari 9 data')
        ->assertSee('q=History%20Class', false);
});

test('member notifications use server side pagination and read filters', function () {
    [$user] = createPortalMember('PG-PORTAL-PAGINATED-NOTIFICATIONS');

    foreach (range(1, 8) as $index) {
        DatabaseNotification::query()->create([
            'id' => (string) Str::uuid(),
            'type' => 'MemberPaginationNotification',
            'notifiable_type' => User::class,
            'notifiable_id' => $user->id,
            'data' => ['title' => 'Notif Baru '.str_pad((string) $index, 2, '0', STR_PAD_LEFT), 'body' => 'Notifikasi baru member.'],
            'read_at' => null,
            'created_at' => now()->subMinutes($index),
            'updated_at' => now()->subMinutes($index),
        ]);
    }

    DatabaseNotification::query()->create([
        'id' => (string) Str::uuid(),
        'type' => 'MemberPaginationNotification',
        'notifiable_type' => User::class,
        'notifiable_id' => $user->id,
        'data' => ['title' => 'Notif Dibaca 09', 'body' => 'Notifikasi sudah dibaca.'],
        'read_at' => now(),
        'created_at' => now()->subMinutes(9),
        'updated_at' => now()->subMinutes(9),
    ]);

    $otherUser = User::factory()->create(['email' => 'other.notification@example.com', 'phone' => '081299999903']);
    $otherUser->assignRole('member');
    DatabaseNotification::query()->create([
        'id' => (string) Str::uuid(),
        'type' => 'MemberPaginationNotification',
        'notifiable_type' => User::class,
        'notifiable_id' => $otherUser->id,
        'data' => ['title' => 'Notif Member Lain', 'body' => 'Tidak boleh tampil.'],
        'read_at' => null,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $this->actingAs($user)->get('/member/notifikasi')
        ->assertOk()
        ->assertSee('Menampilkan 1-8 dari 9 data')
        ->assertSee('Tandai Semua Dibaca')
        ->assertSee('Notif Baru 01')
        ->assertDontSee('Notif Dibaca 09')
        ->assertDontSee('Notif Member Lain');

    $this->actingAs($user)->get('/member/notifikasi?page=2')
        ->assertOk()
        ->assertSee('Menampilkan 9-9 dari 9 data')
        ->assertSee('Notif Dibaca 09');

    $this->actingAs($user)->get('/member/notifikasi?status=dibaca')
        ->assertOk()
        ->assertSee('Menampilkan 1-1 dari 1 data')
        ->assertSee('Notif Dibaca 09')
        ->assertDontSee('Notif Baru 01');

    $this->actingAs($user)->get('/member/notifikasi?status=baru')
        ->assertOk()
        ->assertSee('Menampilkan 1-8 dari 8 data')
        ->assertSee('Notif Baru 08')
        ->assertDontSee('Notif Dibaca 09');
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
        ->assertSee('QR aktif')
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
