<?php

use App\Models\ClassEnrollment;
use App\Models\ClassSchedule;
use App\Models\GymClass;
use App\Models\Member;
use App\Models\MemberPackageSession;
use App\Models\Membership;
use App\Models\Package as ServicePackage;
use App\Models\Payment;
use App\Models\Trainer;
use App\Models\User;
use Carbon\CarbonImmutable;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Tests\Feature\Member\Support\MemberPortalFixtures as MemberFixtures;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

// Helper for new tests below — appended to existing suite.

test('booking page disables button and shows kuota habis when capacity is full', function () {
    [$user, $member] = MemberFixtures::improvementsMember('PG-PORTAL-FULL-CAPACITY');

    $package = ServicePackage::create([
        'name' => 'Senam Capacity Test',
        'slug' => 'senam-capacity-test',
        'package_kind' => 'membership',
        'type' => 'senam',
        'price' => 249000,
        'duration_days' => 30,
        'is_active' => true,
    ]);

    Membership::create([
        'member_id' => $member->id,
        'package_id' => $package->id,
        'code' => 'MBR-FULL-0001',
        'start_date' => now()->subDay()->toDateString(),
        'end_date' => now()->addMonth()->toDateString(),
        'price' => 249000,
        'status' => 'active',
    ]);

    $gymClass = GymClass::create([
        'name' => 'Aerobic Full',
        'slug' => 'aerobic-full',
        'class_type' => 'senam',
        'access_type' => 'included',
        'required_package_type' => 'senam',
        'capacity' => 1,
        'is_active' => true,
    ]);

    $sessionDate = CarbonImmutable::today()->next(CarbonImmutable::MONDAY);

    $schedule = ClassSchedule::create([
        'gym_class_id' => $gymClass->id,
        'day_of_week' => $sessionDate->dayOfWeekIso,
        'start_time' => '17:00:00',
        'end_time' => '18:00:00',
        'capacity' => 1,
        'is_active' => true,
    ]);

    // Fill the only slot with another member.
    $otherUser = User::factory()->create(['email' => 'other.full@example.com', 'phone' => '081299999903']);
    $otherUser->assignRole('member');
    $otherMember = Member::create([
        'user_id' => $otherUser->id,
        'member_code' => 'PG-PORTAL-OTHER-FULL',
        'gender' => 'female',
        'birth_date' => '1998-01-01',
        'joined_at' => now()->subMonth()->toDateString(),
        'status' => 'active',
    ]);

    ClassEnrollment::create([
        'schedule_id' => $schedule->id,
        'member_id' => $otherMember->id,
        'session_date' => $sessionDate->toDateString(),
        'status' => 'booked',
    ]);

    $this->actingAs($user)->get('/member/booking-kelas')
        ->assertOk()
        ->assertSee('Kuota Habis')
        ->assertSee('Aerobic Full');
});

test('booking page shows pay button label and price for paid class', function () {
    [$user] = MemberFixtures::improvementsMember('PG-PORTAL-PAID-LABEL');

    $gymClass = GymClass::create([
        'name' => 'Yoga Paid Test',
        'slug' => 'yoga-paid-test',
        'class_type' => 'yoga',
        'access_type' => 'paid',
        'required_package_type' => null,
        'capacity' => 25,
        'member_price' => 75000,
        'non_member_price' => 100000,
        'is_active' => true,
    ]);

    $sessionDate = CarbonImmutable::today()->next(CarbonImmutable::TUESDAY);

    ClassSchedule::create([
        'gym_class_id' => $gymClass->id,
        'day_of_week' => $sessionDate->dayOfWeekIso,
        'start_time' => '08:00:00',
        'end_time' => '09:00:00',
        'capacity' => 25,
        'is_active' => true,
    ]);

    $this->actingAs($user)->get('/member/booking-kelas')
        ->assertOk()
        ->assertSee('Yoga Paid Test')
        ->assertSee('Booking & Bayar Rp 75.000')
        ->assertSee('Biaya Kelas');
});

test('checkout pt package requires trainer selection matching specialization', function () {
    [$user, $member] = MemberFixtures::improvementsMember('PG-PORTAL-PT-TRAINER');
    MemberFixtures::makeCheckoutEligible($user);

    $gymPackage = ServicePackage::create([
        'name' => 'Gym For PT Trainer Test',
        'slug' => 'gym-for-pt-trainer-test',
        'package_kind' => 'membership',
        'type' => 'gym',
        'price' => 249000,
        'duration_days' => 30,
        'is_active' => true,
    ]);

    Membership::create([
        'member_id' => $member->id,
        'package_id' => $gymPackage->id,
        'code' => 'MBR-PT-TRAINER-0001',
        'start_date' => now()->subDay()->toDateString(),
        'end_date' => now()->addMonth()->toDateString(),
        'price' => 249000,
        'status' => 'active',
    ]);

    $package = ServicePackage::create([
        'name' => 'PT Trainer Test',
        'slug' => 'pt-trainer-test',
        'package_kind' => 'personal_trainer',
        'type' => 'pt',
        'price' => 650000,
        'session_count' => 5,
        'requires_active_membership' => false,
        'is_active' => true,
    ]);

    $ptTrainer = Trainer::create([
        'name' => 'Coach PT One',
        'specialization' => 'Personal Trainer',
        'is_active' => true,
    ]);

    $muaythaiTrainer = Trainer::create([
        'name' => 'Coach Muay One',
        'specialization' => 'Muaythai',
        'is_active' => true,
    ]);

    // Empty trainer_id should fail validation.
    $this->actingAs($user)->post(route('member.package-sessions.checkout', $package))
        ->assertSessionHasErrors(['trainer_id']);

    // Wrong specialization (Muaythai trainer for PT package) should fail.
    $this->actingAs($user)->post(route('member.package-sessions.checkout', $package), [
        'trainer_id' => $muaythaiTrainer->id,
    ])->assertSessionHasErrors(['trainer_id']);

    // Correct specialization should reach checkout (and fail later because Midtrans is not configured).
    config(['services.midtrans.server_key' => 'server-test-key']);
    Http::fake([
        'app.sandbox.midtrans.com/*' => Http::response([
            'token' => 'snap-token-pt',
            'redirect_url' => 'https://sandbox.midtrans.test/pay-pt',
        ], 201),
    ]);

    $this->actingAs($user)->post(route('member.package-sessions.checkout', $package), [
        'trainer_id' => $ptTrainer->id,
    ])->assertSessionHasNoErrors();

    expect(MemberPackageSession::query()
        ->where('member_id', $member->id)
        ->where('package_id', $package->id)
        ->where('trainer_id', $ptTrainer->id)
        ->exists()
    )->toBeTrue();
});

test('membership page shows trainer dropdown only for pt and muaythai packages', function () {
    [$user, $member] = MemberFixtures::improvementsMember('PG-PORTAL-TRAINER-DROPDOWN');
    MemberFixtures::makeCheckoutEligible($user);

    $gymPackage = ServicePackage::create([
        'name' => 'Gym Dropdown Active Test',
        'slug' => 'gym-dropdown-active-test',
        'package_kind' => 'membership',
        'type' => 'gym',
        'price' => 249000,
        'duration_days' => 30,
        'is_active' => true,
    ]);

    Membership::create([
        'member_id' => $member->id,
        'package_id' => $gymPackage->id,
        'code' => 'MBR-TRAINER-DROPDOWN-0001',
        'start_date' => now()->subDay()->toDateString(),
        'end_date' => now()->addMonth()->toDateString(),
        'price' => 249000,
        'status' => 'active',
    ]);

    ServicePackage::create([
        'name' => 'PT Dropdown Test',
        'slug' => 'pt-dropdown-test',
        'package_kind' => 'personal_trainer',
        'type' => 'pt',
        'price' => 650000,
        'session_count' => 5,
        'is_active' => true,
    ]);

    ServicePackage::create([
        'name' => 'Gym Dropdown Test',
        'slug' => 'gym-dropdown-test',
        'package_kind' => 'membership',
        'type' => 'gym',
        'price' => 249000,
        'duration_days' => 30,
        'is_active' => true,
    ]);

    Trainer::create([
        'name' => 'Coach Dropdown',
        'specialization' => 'Personal Trainer',
        'is_active' => true,
    ]);

    $this->actingAs($user)->get('/member/membership')
        ->assertOk()
        ->assertSee('Pilih Trainer')
        ->assertSee('Coach Dropdown')
        ->assertSee('PT Dropdown Test')
        ->assertSee('Gym Dropdown Test');
});

test('transactions list shows service column for membership and class enrollment', function () {
    [$user, $member] = MemberFixtures::improvementsMember('PG-PORTAL-TX-SERVICE');

    $package = ServicePackage::create([
        'name' => 'Gym Service Column',
        'slug' => 'gym-service-column',
        'package_kind' => 'membership',
        'type' => 'gym',
        'price' => 249000,
        'duration_days' => 30,
        'is_active' => true,
    ]);

    $membership = Membership::create([
        'member_id' => $member->id,
        'package_id' => $package->id,
        'code' => 'MBR-TX-COL-0001',
        'start_date' => now()->toDateString(),
        'end_date' => now()->addMonth()->toDateString(),
        'price' => 249000,
        'status' => 'pending_payment',
    ]);

    Payment::create([
        'payment_code' => 'PAY-TX-COL-0001',
        'member_id' => $member->id,
        'payable_type' => Membership::class,
        'payable_id' => $membership->id,
        'method' => 'midtrans',
        'amount' => 249000,
        'status' => 'waiting_payment',
    ]);

    $this->actingAs($user)->get('/member/transaksi')
        ->assertOk()
        ->assertSee('Layanan')
        ->assertSee('Gym Service Column')
        ->assertSee('Membership');
});

test('notifications page renders internal action links from payload', function () {
    [$user] = MemberFixtures::improvementsMember('PG-PORTAL-NOTIF-ACTION');

    DatabaseNotification::query()->create([
        'id' => (string) Str::uuid(),
        'type' => 'App\\Notifications\\MemberOperationalNotification',
        'notifiable_type' => User::class,
        'notifiable_id' => $user->id,
        'data' => [
            'title' => 'Pembayaran Berhasil',
            'body' => 'Pembayaran sudah lunas.',
            'action_url' => route('member.transactions'),
            'action_label' => 'Lihat Transaksi',
        ],
        'read_at' => null,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DatabaseNotification::query()->create([
        'id' => (string) Str::uuid(),
        'type' => 'App\\Notifications\\MemberOperationalNotification',
        'notifiable_type' => User::class,
        'notifiable_id' => $user->id,
        'data' => [
            'title' => 'Promo Eksternal',
            'body' => 'Lihat detail di luar.',
            'action_url' => 'https://attacker.example.com/phish',
            'action_label' => 'Klik di sini',
        ],
        'read_at' => null,
        'created_at' => now()->subMinute(),
        'updated_at' => now()->subMinute(),
    ]);

    $response = $this->actingAs($user)->get('/member/notifikasi')
        ->assertOk()
        ->assertSee('Pembayaran Berhasil')
        ->assertSee('Lihat Transaksi')
        ->assertSee(route('member.transactions'), false)
        ->assertSee('Promo Eksternal');

    // External action_url must not render as a link.
    expect(str_contains($response->getContent(), 'https://attacker.example.com/phish'))->toBeFalse();
});

test('member sees lunas after midtrans status sync confirms settlement', function () {
    config(['services.midtrans.server_key' => 'server-test-key']);

    [$user, $member] = MemberFixtures::improvementsMember('PG-PORTAL-SYNC');

    $package = ServicePackage::create([
        'name' => 'Gym Sync Test',
        'slug' => 'gym-sync-test',
        'package_kind' => 'membership',
        'type' => 'gym',
        'price' => 249000,
        'duration_days' => 30,
        'is_active' => true,
    ]);

    $membership = Membership::create([
        'member_id' => $member->id,
        'package_id' => $package->id,
        'code' => 'MBR-SYNC-0001',
        'start_date' => now()->toDateString(),
        'end_date' => now()->addMonth()->toDateString(),
        'price' => 249000,
        'status' => 'pending_payment',
    ]);

    $payment = Payment::create([
        'payment_code' => 'PAY-SYNC-0001',
        'member_id' => $member->id,
        'payable_type' => Membership::class,
        'payable_id' => $membership->id,
        'method' => 'midtrans',
        'amount' => 249000,
        'status' => 'waiting_payment',
        'midtrans_order_id' => 'PAY-SYNC-0001-120000',
        'midtrans_redirect_url' => 'https://sandbox.midtrans.test/pay-sync',
    ]);

    Http::fake([
        'api.sandbox.midtrans.com/v2/PAY-SYNC-0001-120000/status' => Http::response([
            'order_id' => 'PAY-SYNC-0001-120000',
            'transaction_status' => 'settlement',
            'fraud_status' => 'accept',
            'transaction_id' => 'midtrans-trx-sync-1',
            'payment_type' => 'qris',
            'gross_amount' => '249000.00',
            'status_code' => '200',
        ], 200),
    ]);

    $this->actingAs($user)->get(route('member.transactions.show', $payment))
        ->assertOk()
        ->assertSee('Lunas');

    expect($payment->refresh()->status)->toBe('paid');
    expect($membership->refresh()->status)->toBe('active');
});

test('member layout renders success flash banner with emerald palette and polite live region', function () {
    [$user] = MemberFixtures::improvementsMember('PG-PORTAL-FLASH-OK');

    $this->actingAs($user)
        ->withSession(['status' => 'Profil berhasil diperbarui.', 'status_kind' => 'success'])
        ->get('/member/dashboard')
        ->assertOk()
        ->assertSee('Profil berhasil diperbarui.')
        ->assertSee('border-emerald-500/30', false)
        ->assertSee('aria-live="polite"', false);
});

test('member layout renders error flash banner with red palette and assertive live region', function () {
    [$user] = MemberFixtures::improvementsMember('PG-PORTAL-FLASH-ERR');

    $this->actingAs($user)
        ->withSession(['status' => 'Aksi gagal diproses.', 'status_kind' => 'error'])
        ->get('/member/dashboard')
        ->assertOk()
        ->assertSee('Aksi gagal diproses.')
        ->assertSee('border-red-500/30', false)
        ->assertSee('aria-live="assertive"', false);
});

test('member profile edit dispatches to member account security page using member layout', function () {
    [$user] = MemberFixtures::improvementsMember('PG-PORTAL-ACCT-SEC');

    $this->actingAs($user)->get(route('profile.edit'))
        ->assertOk()
        ->assertSee('Keamanan Akun')
        ->assertSee('Informasi Akun')
        ->assertSee('Ubah Password')
        ->assertSee('member-card', false)
        ->assertSee('member-button-primary', false)
        ->assertSee('Profil Member');
});
