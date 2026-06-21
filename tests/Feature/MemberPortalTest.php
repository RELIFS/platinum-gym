<?php

use App\Features\MemberPortal\Actions\UpdateMemberProfileAction;
use App\Features\Payments\Actions\FulfillPaidPaymentAction;
use App\Models\ClassEnrollment;
use App\Models\ClassSchedule;
use App\Models\GymCheckIn;
use App\Models\GymClass;
use App\Models\Member;
use App\Models\MemberPackageSession;
use App\Models\MemberPackageSessionUsage;
use App\Models\Membership;
use App\Models\Package as ServicePackage;
use App\Models\Payment;
use App\Models\QrToken;
use App\Models\Trainer;
use App\Models\User;
use Carbon\CarbonImmutable;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

function createPortalMember(string $code = 'PG-PORTAL-0001', ?string $email = null, ?string $phone = null): array
{
    $user = User::factory()->create([
        'name' => 'Andi Portal',
        'email' => $email ?? 'andi.portal@example.com',
        'phone' => $phone ?? '081234567890',
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

function makePortalMemberCheckoutEligible(User $user): void
{
    $user->forceFill(['avatar' => 'storage/member/avatars/test-avatar.jpg'])->save();
}

test('member portal routes require authentication', function (string $path) {
    $this->get($path)->assertRedirect('/login');
})->with([
    '/member/dashboard',
    '/member/profil',
    '/member/profil/edit',
    '/member/membership',
    '/member/booking-kelas',
    '/member/riwayat-booking',
    '/member/transaksi',
    '/member/qr',
    '/member/qr/download',
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
    ['/member/profil/edit', 'Edit Profil'],
    ['/member/membership', 'Membership'],
    ['/member/booking-kelas', 'Booking Kelas'],
    ['/member/riwayat-booking', 'Riwayat Booking'],
    ['/member/transaksi', 'Transaksi'],
    ['/member/qr', 'QR Member'],
    ['/member/notifikasi', 'Notifikasi'],
]);

test('member mobile navigation keeps neutral hamburger styling', function () {
    [$user] = createPortalMember('PG-PORTAL-HAMBURGER');

    $this->actingAs($user)->get('/member/dashboard')
        ->assertOk()
        ->assertSee('member-mobile-menu-button', false)
        ->assertSee('aria-controls="member-mobile-navigation"', false)
        ->assertDontSee('bg-zinc-950 text-white', false);
});

test('member profile uses nim wording and system check helper', function () {
    [$user, $member] = createPortalMember('PG-PORTAL-NIM-COPY');
    $member->forceFill([
        'is_student' => true,
        'student_id_number' => '2200012345',
        'student_verification_status' => 'pending_review',
    ])->save();

    $this->actingAs($user)->get(route('member.profile.edit'))
        ->assertOk()
        ->assertSee('Nomor Induk Mahasiswa (NIM)')
        ->assertSee('Masukkan Nomor Induk Mahasiswa (NIM) yang terdaftar di PDDIKTI, karena data akan dicek secara otomatis oleh sistem.')
        ->assertDontSee('No. Identitas Mahasiswa')
        ->assertDontSee('nomor identitas mahasiswa')
        ->assertDontSee('Admin akan memverifikasi data ini sebelum checkout paket mahasiswa.');

    $this->actingAs($user)->get(route('member.profile'))
        ->assertOk()
        ->assertSee('NIM')
        ->assertDontSee('No. Identitas Mahasiswa');
});

test('student profile validation uses nim attribute', function () {
    [$user] = createPortalMember('PG-PORTAL-NIM-VALIDATION');

    $this->actingAs($user)->from(route('member.profile.edit'))->patch(route('member.profile.update'), [
        'name' => 'Andi Portal',
        'email' => 'andi.portal@example.com',
        'phone' => '081234567890',
        'gender' => 'male',
        'birth_date' => '2000-01-01',
        'is_student' => '1',
    ])->assertRedirect(route('member.profile.edit'))
        ->assertSessionHasErrors(['student_id_number' => 'The NIM field is required.']);
});

test('member can update profile from member portal', function () {
    [$user, $member] = createPortalMember('PG-PORTAL-UPDATE');

    $this->actingAs($user)->patch(route('member.profile.update'), [
        'name' => 'Andi Updated',
        'email' => 'andi.updated@example.com',
        'phone' => '+62 812-3456-7899',
        'gender' => 'female',
        'birth_day' => '3',
        'birth_month' => '2',
        'birth_year' => '1999',
        'address' => 'Jl. Khatib Sulaiman No. 10',
        'emergency_contact' => '6281299990001',
        'is_student' => '1',
        'student_id_number' => 'MHS-12345',
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
        ->student_verification_status->toBe('pending_review');
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

    $response = $this->actingAs($user)->get('/member/dashboard');

    $response
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

    preg_match('/<section\b(?=[^>]*data-chatbot-panel)[^>]*class="([^"]*)"/', $response->getContent(), $matches);

    expect(explode(' ', $matches[1]))
        ->not->toContain('flex')
        ->not->toContain('bg-zinc-950')
        ->not->toContain('border-zinc-800')
        ->toContain('flex-col');
});

test('member light theme uses balanced soft surfaces without dominant dark cards', function () {
    [$user] = createPortalMember('PG-PORTAL-LIGHT-CONTRAST');

    $dashboard = $this->actingAs($user)->get('/member/dashboard');

    $dashboard
        ->assertOk()
        ->assertSee('member-card-pass relative isolate overflow-hidden', false)
        ->assertSee('border-r border-zinc-200 bg-white text-zinc-950', false)
        ->assertDontSee('border border-zinc-800 bg-zinc-950', false)
        ->assertDontSee('text-xs font-black uppercase tracking-[0.2em] text-gold-400', false)
        ->assertDontSee('rounded-lg border border-white/10 bg-white/[0.06] p-4', false)
        ->assertDontSee('rounded-md bg-zinc-950', false);

    $this->actingAs($user)->get('/member/profil')
        ->assertOk()
        ->assertSee('member-card-strong relative isolate overflow-hidden', false)
        ->assertSee('text-zinc-950 dark:text-white', false)
        ->assertDontSee('text-xs font-black uppercase tracking-[0.2em] text-gold-400', false)
        ->assertDontSee('rounded-lg border border-white/10 bg-white/[0.06] p-4', false);

    $this->actingAs($user)->get('/member/membership')
        ->assertOk()
        ->assertSee('member-card-strong relative isolate overflow-hidden', false)
        ->assertSee('member-title', false)
        ->assertSee('member-form-input', false)
        ->assertDontSee('text-3xl font-black leading-tight tracking-tight text-white', false);

    $this->actingAs($user)->get('/member/transaksi')
        ->assertOk()
        ->assertSee('member-form-input', false)
        ->assertDontSee('bg-slate-900', false);

    $this->actingAs($user)->get('/member/qr')
        ->assertOk()
        ->assertSee('data-qr-member-visual', false)
        ->assertSee('lg:grid-cols-[minmax(0,0.95fr)_minmax(0,1.05fr)]', false)
        ->assertSee('member-card-pass mx-auto w-full max-w-sm min-w-0 text-center lg:max-w-none', false)
        ->assertSee('text-zinc-950 dark:text-white', false)
        ->assertDontSee('text-2xl font-black text-white', false)
        ->assertDontSee('shadow-[0_24px_70px_rgba(0,0,0,0.32)]', false);

    $css = file_get_contents(resource_path('css/app.css'));
    $memberChatbot = file_get_contents(resource_path('views/member/partials/chatbot.blade.php'));

    expect($css)
        ->toContain('.member-card-strong')
        ->toContain('border border-zinc-200 bg-white')
        ->toContain('.member-card-pass')
        ->toContain('border border-gold-500/25 bg-white')
        ->toContain('.member-form-input')
        ->toContain('.member-list-card')
        ->toContain('.member-table-wrap')
        ->not->toContain('.member-card-pass {'.PHP_EOL.'        @apply min-w-0 rounded-lg border border-zinc-800 bg-zinc-950')
        ->and($memberChatbot)
        ->toContain('border border-zinc-200 bg-white')
        ->toContain('dark:border-zinc-800 dark:bg-zinc-950')
        ->not->toContain('class="mx-auto h-[min(620px,calc(100dvh-1.5rem))] w-full flex-col overflow-hidden overscroll-contain rounded-2xl border border-zinc-800 bg-zinc-950');
});

test('member portal hides exhausted and expired package sessions', function () {
    [$user, $member] = createPortalMember('PG-PORTAL-SESSIONS');

    $trainer = Trainer::create([
        'name' => 'Coach Adi',
        'specialty' => 'Muaythai',
        'status' => 'active',
    ]);

    $package = ServicePackage::create([
        'name' => 'Muaythai Mahasiswa 4x',
        'slug' => 'muaythai-mahasiswa-4x-portal',
        'package_kind' => 'session',
        'type' => 'muaythai',
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
        'trainer_id' => $trainer->id,
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
        ->assertSee('Muaythai Mahasiswa 4x')
        ->assertSee('3 dari 4 sesi tersisa')
        ->assertSee('Coach Adi')
        ->assertDontSee('Coach Coach Adi')
        ->assertDontSee('MPS-EMPTY-0001')
        ->assertDontSee('MPS-EXPIRED-0001')
        ->assertDontSee('PT Habis Portal')
        ->assertDontSee('PT Expired Portal');
});

test('membership page shows all active memberships in horizontal rail without qr shortcut', function () {
    [$user, $member] = createPortalMember('PG-PORTAL-ACTIVE-RAIL');
    makePortalMemberCheckoutEligible($user);

    $gymPackage = ServicePackage::create([
        'name' => 'Gym Aktif Rail Test',
        'slug' => 'gym-aktif-rail-test',
        'package_kind' => 'membership',
        'type' => 'gym',
        'category' => 'umum',
        'price' => 250000,
        'duration_days' => 30,
        'is_active' => true,
    ]);

    $senamPackage = ServicePackage::create([
        'name' => 'Senam Aktif Rail Test',
        'slug' => 'senam-aktif-rail-test',
        'package_kind' => 'membership',
        'type' => 'senam',
        'category' => 'umum',
        'price' => 200000,
        'duration_days' => 30,
        'is_active' => true,
    ]);

    $expiredPackage = ServicePackage::create([
        'name' => 'Gym Expired Rail Test',
        'slug' => 'gym-expired-rail-test',
        'package_kind' => 'membership',
        'type' => 'gym',
        'category' => 'umum',
        'price' => 150000,
        'duration_days' => 30,
        'is_active' => false,
    ]);

    Membership::create([
        'member_id' => $member->id,
        'package_id' => $gymPackage->id,
        'code' => 'MBR-RAIL-GYM-0001',
        'start_date' => now()->subDay()->toDateString(),
        'end_date' => now()->addDays(20)->toDateString(),
        'price' => 250000,
        'status' => 'active',
    ]);

    Membership::create([
        'member_id' => $member->id,
        'package_id' => $senamPackage->id,
        'code' => 'MBR-RAIL-SENAM-0001',
        'start_date' => now()->subDay()->toDateString(),
        'end_date' => now()->addDays(10)->toDateString(),
        'price' => 200000,
        'status' => 'active',
    ]);

    Membership::create([
        'member_id' => $member->id,
        'package_id' => $expiredPackage->id,
        'code' => 'MBR-RAIL-EXPIRED-0001',
        'start_date' => now()->subMonth()->toDateString(),
        'end_date' => now()->subDay()->toDateString(),
        'price' => 150000,
        'status' => 'active',
    ]);

    $this->actingAs($user)->get(route('member.membership'))
        ->assertOk()
        ->assertSee('member-horizontal-rail', false)
        ->assertSee('snap-x snap-mandatory', false)
        ->assertSee('2 paket aktif')
        ->assertSee('Gym Aktif Rail Test')
        ->assertSee('Senam Aktif Rail Test')
        ->assertDontSee('Gym Expired Rail Test')
        ->assertDontSee('Lihat QR Member');
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
    makePortalMemberCheckoutEligible($user);

    foreach (range(1, 7) as $index) {
        ServicePackage::create([
            'name' => 'Paket Member '.str_pad((string) $index, 2, '0', STR_PAD_LEFT),
            'slug' => 'paket-member-page-'.$index,
            'package_kind' => 'membership',
            'type' => 'gym',
            'category' => 'umum',
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
        ->assertSee('Menampilkan 1-8 dari 8 data')
        ->assertSee('Kategori Umum')
        ->assertSee('Paket Member 01')
        ->assertSee('Paket Member 07')
        ->assertSee('PT Filter Member');

    $this->actingAs($user)->get('/member/membership?kind=membership')
        ->assertOk()
        ->assertSee('Menampilkan 1-7 dari 7 data')
        ->assertSee('kind=membership', false)
        ->assertDontSee('PT Filter Member');

    $this->actingAs($user)->get('/member/membership?q=Paket%20Member%2007')
        ->assertOk()
        ->assertSee('Menampilkan 1-1 dari 1 data')
        ->assertSee('Paket Member 07')
        ->assertSee('Checkout Membership');
});

test('membership page groups packages and hides empty poundfit section', function () {
    [$user] = createPortalMember('PG-PORTAL-MEMBERSHIP-GROUPS');
    makePortalMemberCheckoutEligible($user);

    ServicePackage::create([
        'name' => 'Gym Umum Group Test',
        'slug' => 'gym-umum-group-test',
        'package_kind' => 'membership',
        'type' => 'gym',
        'category' => 'umum',
        'price' => 249000,
        'duration_days' => 30,
        'is_active' => true,
    ]);

    ServicePackage::create([
        'name' => 'Gym Mahasiswa Group Test',
        'slug' => 'gym-mahasiswa-group-test',
        'package_kind' => 'membership',
        'type' => 'gym',
        'category' => 'mahasiswa',
        'max_age' => 22,
        'price' => 199000,
        'duration_days' => 30,
        'is_active' => true,
    ]);

    ServicePackage::create([
        'name' => 'PT Group Test',
        'slug' => 'pt-group-test',
        'package_kind' => 'personal_trainer',
        'type' => 'pt',
        'price' => 650000,
        'session_count' => 5,
        'is_active' => true,
    ]);

    $this->actingAs($user)->get(route('member.membership'))
        ->assertOk()
        ->assertSee('Status membership')
        ->assertSee('Pilih layanan')
        ->assertSee('Kategori Umum')
        ->assertSee('Gym Umum Group Test')
        ->assertSee('Kategori Mahasiswa')
        ->assertSee('Gym Mahasiswa Group Test')
        ->assertSee('Personal Trainer')
        ->assertSee('PT Group Test')
        ->assertDontSee('Poundfit');
});

test('poundfit package appears in membership catalog and activates as package session after payment', function () {
    config(['services.midtrans.server_key' => 'server-test-key']);

    Http::fake([
        'app.sandbox.midtrans.com/*' => Http::response([
            'token' => 'snap-token-poundfit-session',
            'redirect_url' => 'https://sandbox.midtrans.test/pay-poundfit-session',
        ], 201),
    ]);

    [$user, $member] = createPortalMember('PG-PORTAL-POUNDFIT-PACKAGE');

    $package = ServicePackage::create([
        'name' => 'Poundfit 1x',
        'slug' => 'poundfit-1x-test',
        'package_kind' => 'session',
        'type' => 'poundfit',
        'category' => 'umum',
        'price' => 50000,
        'session_count' => 1,
        'is_active' => true,
    ]);

    $this->actingAs($user)->get(route('member.membership'))
        ->assertOk()
        ->assertSee('Poundfit')
        ->assertSee('Poundfit 1x')
        ->assertSee('Rp 50.000')
        ->assertSee('Checkout Paket Sesi');

    $response = $this->actingAs($user)->post(route('member.package-sessions.checkout', $package));
    $payment = Payment::query()->where('member_id', $member->id)->firstOrFail();
    $session = MemberPackageSession::query()->where('member_id', $member->id)->where('package_id', $package->id)->firstOrFail();

    $response->assertRedirect(route('member.transactions.show', $payment));

    expect($session)
        ->status->toBe('pending_payment')
        ->total_sessions->toBe(1)
        ->remaining_sessions->toBe(1)
        ->and((float) $payment->amount)->toBe(50000.0);

    app(FulfillPaidPaymentAction::class)->handle($payment);

    expect($session->refresh())
        ->status->toBe('active')
        ->remaining_sessions->toBe(1);
});

test('gym plus senam package is disabled for male members and available for female members', function () {
    config(['services.midtrans.server_key' => 'server-test-key']);
    Http::fake([
        'app.sandbox.midtrans.com/*' => Http::response([
            'token' => 'snap-token-include-female',
            'redirect_url' => 'https://sandbox.midtrans.test/pay-include-female',
        ], 201),
    ]);

    [$maleUser, $maleMember] = createPortalMember('PG-PORTAL-INCLUDE-MALE', 'include.male@example.com', '081211110001');
    makePortalMemberCheckoutEligible($maleUser);

    [$femaleUser, $femaleMember] = createPortalMember('PG-PORTAL-INCLUDE-FEMALE', 'include.female@example.com', '081211110002');
    makePortalMemberCheckoutEligible($femaleUser);
    $femaleMember->forceFill(['gender' => 'female'])->save();

    $package = ServicePackage::create([
        'name' => 'Gym + Senam Female Guard Test',
        'slug' => 'gym-senam-female-guard-test',
        'package_kind' => 'membership',
        'type' => 'include',
        'category' => 'umum',
        'gender_restriction' => 'female',
        'price' => 250000,
        'duration_days' => 30,
        'is_active' => true,
    ]);

    $this->actingAs($maleUser)->get(route('member.membership'))
        ->assertOk()
        ->assertSee('Paket ini khusus member perempuan.')
        ->assertSee('Khusus Perempuan');

    $this->actingAs($maleUser)->post(route('member.membership.checkout', $package))
        ->assertRedirect(route('member.profile.edit'));

    $this->actingAs($femaleUser)->post(route('member.membership.checkout', $package));

    expect(Membership::query()->where('member_id', $maleMember->id)->count())->toBe(0)
        ->and(Payment::query()->where('member_id', $maleMember->id)->count())->toBe(0)
        ->and(Payment::query()->where('member_id', $femaleMember->id)->count())->toBe(1);
});

test('personal trainer package requires active gym or include membership', function () {
    config(['services.midtrans.server_key' => 'server-test-key']);
    Http::fake([
        'app.sandbox.midtrans.com/*' => Http::response([
            'token' => 'snap-token-pt-guard',
            'redirect_url' => 'https://sandbox.midtrans.test/pay-pt-guard',
        ], 201),
    ]);

    [$noGymUser, $noGymMember] = createPortalMember('PG-PORTAL-PT-NO-GYM', 'pt.no.gym@example.com', '081222220001');
    [$senamUser, $senamMember] = createPortalMember('PG-PORTAL-PT-SENAM', 'pt.senam@example.com', '081222220002');
    [$gymUser, $gymMember] = createPortalMember('PG-PORTAL-PT-GYM', 'pt.gym@example.com', '081222220003');

    $senamPackage = ServicePackage::create([
        'name' => 'Senam PT Guard Test',
        'slug' => 'senam-pt-guard-test',
        'package_kind' => 'membership',
        'type' => 'senam',
        'price' => 249000,
        'duration_days' => 30,
        'is_active' => true,
    ]);

    $gymPackage = ServicePackage::create([
        'name' => 'Gym PT Guard Test',
        'slug' => 'gym-pt-guard-test',
        'package_kind' => 'membership',
        'type' => 'gym',
        'price' => 249000,
        'duration_days' => 30,
        'is_active' => true,
    ]);

    $ptPackage = ServicePackage::create([
        'name' => 'PT Gym Required Test',
        'slug' => 'pt-gym-required-test',
        'package_kind' => 'personal_trainer',
        'type' => 'pt',
        'price' => 650000,
        'session_count' => 5,
        'is_active' => true,
    ]);

    $trainer = Trainer::create([
        'name' => 'Coach PT Guard',
        'specialization' => 'Personal Trainer',
        'is_active' => true,
    ]);

    Membership::create([
        'member_id' => $senamMember->id,
        'package_id' => $senamPackage->id,
        'code' => 'MBR-PT-SENAM-0001',
        'start_date' => now()->subDay()->toDateString(),
        'end_date' => now()->addMonth()->toDateString(),
        'price' => 249000,
        'status' => 'active',
    ]);

    Membership::create([
        'member_id' => $gymMember->id,
        'package_id' => $gymPackage->id,
        'code' => 'MBR-PT-GYM-0001',
        'start_date' => now()->subDay()->toDateString(),
        'end_date' => now()->addMonth()->toDateString(),
        'price' => 249000,
        'status' => 'active',
    ]);

    $this->actingAs($noGymUser)->get(route('member.membership'))
        ->assertOk()
        ->assertSee('Personal Trainer hanya tersedia untuk member dengan membership Gym aktif.')
        ->assertSee('Butuh Membership Gym');

    $this->actingAs($noGymUser)->post(route('member.package-sessions.checkout', $ptPackage), [
        'trainer_id' => $trainer->id,
    ])->assertSessionHasNoErrors();

    $this->actingAs($senamUser)->post(route('member.package-sessions.checkout', $ptPackage), [
        'trainer_id' => $trainer->id,
    ])->assertSessionHasNoErrors();

    $this->actingAs($gymUser)->post(route('member.package-sessions.checkout', $ptPackage), [
        'trainer_id' => $trainer->id,
    ])->assertSessionHasNoErrors();

    expect(MemberPackageSession::query()->where('member_id', $noGymMember->id)->exists())->toBeFalse()
        ->and(MemberPackageSession::query()->where('member_id', $senamMember->id)->exists())->toBeFalse()
        ->and(MemberPackageSession::query()->where('member_id', $gymMember->id)->exists())->toBeTrue();
});

test('member class schedule uses server side pagination and filters', function () {
    [$user] = createPortalMember('PG-PORTAL-PAGINATED-SCHEDULES');

    foreach (range(1, 25) as $index) {
        $accessType = $index >= 24 ? 'paid' : 'included';
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
        ->assertSee('Menampilkan 1-24 dari 25 data')
        ->assertSee('Jadwal Member 01')
        ->assertSee('Jadwal Member 24')
        ->assertDontSee('Jadwal Member 25')
        ->assertDontSee('<option value="paid">Bayar Kelas</option>', false);

    $this->actingAs($user)->get('/member/booking-kelas?page=2')
        ->assertOk()
        ->assertSee('Menampilkan 25-25 dari 25 data')
        ->assertSee('Jadwal Member 25');

    $this->actingAs($user)->get('/member/booking-kelas?access=paid')
        ->assertOk()
        ->assertSee('Menampilkan 1-2 dari 2 data')
        ->assertSee('Jadwal Member 24')
        ->assertSee('Jadwal Member 25')
        ->assertDontSee('Jadwal Member 01');

    $this->actingAs($user)->get('/member/booking-kelas?q=Jadwal%20Member%2025')
        ->assertOk()
        ->assertSee('Menampilkan 1-1 dari 1 data')
        ->assertSee('Jadwal Member 25')
        ->assertSee('Booking Kelas');
});

test('member class schedule groups by class type sections', function () {
    [$user] = createPortalMember('PG-PORTAL-SCHEDULE-GROUPS');

    $classes = [
        ['Aerobic Group Test', 'senam', 'included', 'senam', 1, '17:00:00'],
        ['Zumba Group Test', 'senam', 'included', 'senam', 2, '17:00:00'],
        ['Muaythai Group Test', 'muaythai', 'session_based', 'muaythai', 3, '19:00:00'],
        ['Poundfit Group Test', 'poundfit', 'session_based', 'poundfit', 4, '19:15:00'],
    ];

    foreach ($classes as [$name, $classType, $accessType, $requiredType, $day, $startTime]) {
        $gymClass = GymClass::create([
            'name' => $name,
            'slug' => Str::slug($name),
            'class_type' => $classType,
            'access_type' => $accessType,
            'required_package_type' => $requiredType,
            'capacity' => 20,
            'is_active' => true,
        ]);

        ClassSchedule::create([
            'gym_class_id' => $gymClass->id,
            'day_of_week' => $day,
            'start_time' => $startTime,
            'end_time' => '20:00:00',
            'capacity' => 20,
            'is_active' => true,
        ]);
    }

    $this->actingAs($user)->get('/member/booking-kelas')
        ->assertOk()
        ->assertSee('Aerobic')
        ->assertSee('Zumba')
        ->assertSee('Muaythai')
        ->assertSee('Poundfit')
        ->assertSee('Aerobic Group Test')
        ->assertSee('Zumba Group Test')
        ->assertSee('Muaythai Group Test')
        ->assertSee('Poundfit Group Test');
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
        ->assertSee('Batalkan Jadwal Booking')
        ->assertDontSee('>Batalkan Booking<', false)
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
    config(['services.midtrans.server_key' => null]);

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
    makePortalMemberCheckoutEligible($user);

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

test('membership checkout requires complete profile avatar before creating payment', function () {
    config(['services.midtrans.server_key' => 'server-test-key']);
    Http::fake();

    [$user, $member] = createPortalMember('PG-PORTAL-CHECKOUT-NEEDS-AVATAR');

    $package = ServicePackage::create([
        'name' => 'Gym Needs Avatar Test',
        'slug' => 'gym-needs-avatar-test',
        'package_kind' => 'membership',
        'type' => 'gym',
        'price' => 250000,
        'duration_days' => 30,
        'is_active' => true,
    ]);

    $this->actingAs($user)->post(route('member.membership.checkout', $package))
        ->assertRedirect(route('member.profile.edit'));

    expect(Membership::query()->where('member_id', $member->id)->count())->toBe(0)
        ->and(Payment::query()->where('member_id', $member->id)->count())->toBe(0);
});

test('student membership checkout opens after nim profile and avatar are complete', function () {
    config(['services.midtrans.server_key' => 'server-test-key']);
    Http::fake([
        'app.sandbox.midtrans.com/*' => Http::response([
            'token' => 'snap-token-student',
            'redirect_url' => 'https://sandbox.midtrans.test/pay-student',
        ], 201),
    ]);

    [$user, $member] = createPortalMember('PG-PORTAL-STUDENT-CHECKOUT');
    makePortalMemberCheckoutEligible($user);
    $member->forceFill([
        'birth_date' => now()->subYears(20)->toDateString(),
        'is_student' => true,
        'student_id_number' => '2200012345',
        'student_verification_status' => 'pending_review',
    ])->save();

    $package = ServicePackage::create([
        'name' => 'Membership Mahasiswa Test',
        'slug' => 'membership-mahasiswa-test',
        'package_kind' => 'membership',
        'type' => 'gym',
        'category' => 'mahasiswa',
        'price' => 150000,
        'duration_days' => 30,
        'max_age' => 22,
        'is_active' => true,
    ]);

    $this->actingAs($user)->get(route('member.membership'))
        ->assertOk()
        ->assertDontSee('NIM sedang menunggu verifikasi admin.')
        ->assertDontSee('Verifikasi mahasiswa belum selesai.')
        ->assertDontSee('Data mahasiswa belum cocok. Periksa nama lengkap dan NIM.');

    $this->actingAs($user)->post(route('member.membership.checkout', $package));

    expect(Payment::query()->where('member_id', $member->id)->count())->toBe(1);
});

test('student membership checkout accepts unverified status when nim is present', function () {
    config(['services.midtrans.server_key' => 'server-test-key']);
    Http::fake([
        'app.sandbox.midtrans.com/*' => Http::response([
            'token' => 'snap-token-student-unverified',
            'redirect_url' => 'https://sandbox.midtrans.test/pay-student-unverified',
        ], 201),
    ]);

    [$user, $member] = createPortalMember('PG-PORTAL-STUDENT-UNVERIFIED');
    makePortalMemberCheckoutEligible($user);
    $member->forceFill([
        'birth_date' => now()->subYears(20)->toDateString(),
        'is_student' => true,
        'student_id_number' => '2200099999',
        'student_verification_status' => 'unverified',
    ])->save();

    $package = ServicePackage::create([
        'name' => 'Membership Mahasiswa Unverified Test',
        'slug' => 'membership-mahasiswa-unverified-test',
        'package_kind' => 'membership',
        'type' => 'gym',
        'category' => 'mahasiswa',
        'price' => 150000,
        'duration_days' => 30,
        'max_age' => 22,
        'is_active' => true,
    ]);

    $this->actingAs($user)->post(route('member.membership.checkout', $package));

    expect(Payment::query()->where('member_id', $member->id)->count())->toBe(1);
});

test('student membership checkout still requires nim and valid age', function () {
    config(['services.midtrans.server_key' => 'server-test-key']);
    Http::fake();

    [$user, $member] = createPortalMember('PG-PORTAL-STUDENT-NIM-AGE');
    makePortalMemberCheckoutEligible($user);
    $member->forceFill([
        'birth_date' => now()->subYears(20)->toDateString(),
        'is_student' => true,
        'student_id_number' => null,
        'student_verification_status' => 'verified',
    ])->save();

    $package = ServicePackage::create([
        'name' => 'Membership Mahasiswa Guard Test',
        'slug' => 'membership-mahasiswa-guard-test',
        'package_kind' => 'membership',
        'type' => 'gym',
        'category' => 'mahasiswa',
        'price' => 150000,
        'duration_days' => 30,
        'max_age' => 22,
        'is_active' => true,
    ]);

    $this->actingAs($user)->get(route('member.membership'))
        ->assertOk()
        ->assertSee('Lengkapi NIM yang terdaftar di PDDIKTI.')
        ->assertSee('min-h-10', false)
        ->assertSee(route('member.profile.edit'), false);

    $this->actingAs($user)->post(route('member.membership.checkout', $package))
        ->assertRedirect(route('member.profile.edit'));

    $member->forceFill([
        'birth_date' => now()->subYears(23)->subDay()->toDateString(),
        'student_id_number' => '2200012345',
    ])->save();

    $this->actingAs($user)->post(route('member.membership.checkout', $package))
        ->assertRedirect(route('member.profile.edit'));

    expect(Payment::query()->where('member_id', $member->id)->count())->toBe(0);
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

test('poundfit booking requires active poundfit package session and does not consume session on booking', function () {
    [$user, $member] = createPortalMember('PG-PORTAL-POUNDFIT-BOOKING');

    $package = ServicePackage::create([
        'name' => 'Poundfit 1x Booking Test',
        'slug' => 'poundfit-1x-booking-test',
        'package_kind' => 'session',
        'type' => 'poundfit',
        'category' => 'umum',
        'price' => 50000,
        'session_count' => 1,
        'is_active' => true,
    ]);

    $gymClass = GymClass::create([
        'name' => 'Poundfit Booking Test',
        'slug' => 'poundfit-booking-test',
        'class_type' => 'poundfit',
        'access_type' => 'session_based',
        'required_package_type' => 'poundfit',
        'capacity' => 25,
        'is_active' => true,
    ]);

    $sessionDate = CarbonImmutable::today()->next(CarbonImmutable::WEDNESDAY);

    $schedule = ClassSchedule::create([
        'gym_class_id' => $gymClass->id,
        'day_of_week' => $sessionDate->dayOfWeekIso,
        'start_time' => '19:15:00',
        'end_time' => '20:15:00',
        'capacity' => 25,
        'is_active' => true,
    ]);

    $this->actingAs($user)->get(route('member.booking'))
        ->assertOk()
        ->assertSee('Paket Sesi')
        ->assertSee('Booking Kelas')
        ->assertSee('type="submit"', false)
        ->assertDontSee('showUnavailableAlert', false)
        ->assertDontSee('window.alert', false)
        ->assertDontSee('Aktifkan paket Poundfit terlebih dahulu.')
        ->assertDontSee('<p>Aktifkan paket Poundfit terlebih dahulu.</p>', false)
        ->assertDontSee('Lihat Paket Poundfit')
        ->assertDontSee('membership?q=Poundfit', false)
        ->assertDontSee('member-unavailable-note', false);

    $this->actingAs($user)->from(route('member.booking'))->post(route('member.booking.store', $schedule), [
        'session_date' => $sessionDate->toDateString(),
    ])->assertRedirect(route('member.booking'))
        ->assertSessionHas('status', 'Kelas ini membutuhkan membership aktif yang sesuai.')
        ->assertSessionHas('status_kind', 'error');

    expect(ClassEnrollment::query()->where('member_id', $member->id)->where('schedule_id', $schedule->id)->count())->toBe(0);

    $session = MemberPackageSession::create([
        'member_id' => $member->id,
        'package_id' => $package->id,
        'code' => 'MPS-POUNDFIT-BOOK-0001',
        'total_sessions' => 1,
        'used_sessions' => 0,
        'remaining_sessions' => 1,
        'price' => 50000,
        'started_at' => now()->toDateString(),
        'expired_at' => now()->addMonth()->toDateString(),
        'status' => 'active',
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

    expect($session->refresh())
        ->used_sessions->toBe(0)
        ->remaining_sessions->toBe(1);
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

test('member membership checkout reuses open payment on repeated submit', function () {
    config(['services.midtrans.server_key' => 'server-test-key']);

    Http::fake([
        'app.sandbox.midtrans.com/*' => Http::response([
            'token' => 'snap-token-repeat',
            'redirect_url' => 'https://sandbox.midtrans.test/pay-repeat',
        ], 201),
    ]);

    [$user, $member] = createPortalMember('PG-PORTAL-IDEMPOTENT-MBR');
    makePortalMemberCheckoutEligible($user);

    $package = ServicePackage::create([
        'name' => 'Gym Idempotent Test',
        'slug' => 'gym-idempotent-test',
        'package_kind' => 'membership',
        'type' => 'gym',
        'price' => 250000,
        'duration_days' => 30,
        'is_active' => true,
    ]);

    $first = $this->actingAs($user)->post(route('member.membership.checkout', $package));
    $payment = Payment::query()->where('member_id', $member->id)->firstOrFail();
    $second = $this->actingAs($user)->post(route('member.membership.checkout', $package));

    $first->assertRedirect(route('member.transactions.show', $payment));
    $second->assertRedirect(route('member.transactions.show', $payment));

    expect(Membership::query()->where('member_id', $member->id)->where('package_id', $package->id)->count())->toBe(1)
        ->and(Payment::query()->where('member_id', $member->id)->count())->toBe(1)
        ->and($payment->refresh()->invoice)->not->toBeNull();
});

test('member package session checkout reuses open payment on repeated submit', function () {
    config(['services.midtrans.server_key' => 'server-test-key']);

    Http::fake([
        'app.sandbox.midtrans.com/*' => Http::response([
            'token' => 'snap-token-session-repeat',
            'redirect_url' => 'https://sandbox.midtrans.test/pay-session-repeat',
        ], 201),
    ]);

    [$user, $member] = createPortalMember('PG-PORTAL-IDEMPOTENT-SESSION');

    $package = ServicePackage::create([
        'name' => 'Sesi Gym Idempotent',
        'slug' => 'sesi-gym-idempotent',
        'package_kind' => 'session',
        'type' => 'gym',
        'price' => 150000,
        'session_count' => 4,
        'is_active' => true,
    ]);

    $first = $this->actingAs($user)->post(route('member.package-sessions.checkout', $package));
    $payment = Payment::query()->where('member_id', $member->id)->firstOrFail();
    $second = $this->actingAs($user)->post(route('member.package-sessions.checkout', $package));

    $first->assertRedirect(route('member.transactions.show', $payment));
    $second->assertRedirect(route('member.transactions.show', $payment));

    expect(MemberPackageSession::query()->where('member_id', $member->id)->where('package_id', $package->id)->count())->toBe(1)
        ->and(Payment::query()->where('member_id', $member->id)->count())->toBe(1);
});

test('failed midtrans checkout does not leave orphan membership payment or invoice', function () {
    config(['services.midtrans.server_key' => 'server-test-key']);

    Http::fake([
        'app.sandbox.midtrans.com/*' => Http::response(['error_messages' => ['temporary error']], 500),
    ]);

    [$user, $member] = createPortalMember('PG-PORTAL-MIDTRANS-FAIL');
    makePortalMemberCheckoutEligible($user);

    $package = ServicePackage::create([
        'name' => 'Gym Failure Rollback',
        'slug' => 'gym-failure-rollback',
        'package_kind' => 'membership',
        'type' => 'gym',
        'price' => 250000,
        'duration_days' => 30,
        'is_active' => true,
    ]);

    $this->actingAs($user)->post(route('member.membership.checkout', $package))
        ->assertRedirect();

    expect(Membership::query()->where('member_id', $member->id)->count())->toBe(0)
        ->and(Payment::query()->where('member_id', $member->id)->count())->toBe(0);
});

test('paid class repeated booking reuses open payment and enrollment', function () {
    config(['services.midtrans.server_key' => 'server-test-key']);

    Http::fake([
        'app.sandbox.midtrans.com/*' => Http::response([
            'token' => 'snap-token-class-repeat',
            'redirect_url' => 'https://sandbox.midtrans.test/pay-class-repeat',
        ], 201),
    ]);

    [$user, $member] = createPortalMember('PG-PORTAL-PAID-CLASS-IDEMPOTENT');

    $gymClass = GymClass::create([
        'name' => 'Paid Class Idempotent',
        'slug' => 'paid-class-idempotent',
        'class_type' => 'special',
        'access_type' => 'paid',
        'capacity' => 10,
        'member_price' => 75000,
        'is_active' => true,
    ]);

    $sessionDate = CarbonImmutable::today()->next(CarbonImmutable::MONDAY);
    $schedule = ClassSchedule::create([
        'gym_class_id' => $gymClass->id,
        'day_of_week' => $sessionDate->dayOfWeekIso,
        'start_time' => '19:00:00',
        'end_time' => '20:00:00',
        'capacity' => 10,
        'is_active' => true,
    ]);

    $payload = ['session_date' => $sessionDate->toDateString()];
    $first = $this->actingAs($user)->post(route('member.booking.store', $schedule), $payload);
    $payment = Payment::query()->where('member_id', $member->id)->firstOrFail();
    $second = $this->actingAs($user)->post(route('member.booking.store', $schedule), $payload);

    $first->assertRedirect(route('member.transactions.show', $payment));
    $second->assertRedirect(route('member.transactions.show', $payment));

    expect(ClassEnrollment::query()->where('member_id', $member->id)->where('schedule_id', $schedule->id)->count())->toBe(1)
        ->and(Payment::query()->where('member_id', $member->id)->count())->toBe(1);
});

test('member profile view is separate from edit form and avatar can be uploaded', function () {
    Storage::fake('public');

    [$user] = createPortalMember('PG-PORTAL-PROFILE-EDIT');

    $this->actingAs($user)->get(route('member.profile'))
        ->assertOk()
        ->assertSee('Edit Profil')
        ->assertDontSee('id="member_name"', false)
        ->assertDontSee('name="avatar"', false);

    $this->actingAs($user)->get(route('member.profile.edit'))
        ->assertOk()
        ->assertSee('name="avatar"', false)
        ->assertSee('id="member_name"', false)
        ->assertSee('name="birth_date_display"', false)
        ->assertSee('name="birth_date"', false)
        ->assertSee('placeholder="dd/mm/yyyy"', false)
        ->assertSee('aria-label="Pilih tanggal lahir"', false)
        ->assertDontSee('Pilih tanggal lahir sesuai identitas.')
        ->assertDontSee('name="birth_day"', false)
        ->assertDontSee('name="birth_month"', false)
        ->assertDontSee('name="birth_year"', false)
        ->assertSee('setAvatarPreview', false)
        ->assertSee('Preview, belum disimpan');

    $this->actingAs($user)->patch(route('member.profile.update'), [
        'name' => 'Andi Avatar',
        'email' => 'andi.portal@example.com',
        'phone' => '081234567890',
        'gender' => 'male',
        'birth_date' => '2000-01-01',
        'avatar' => UploadedFile::fake()->image('avatar.jpg', 256, 256)->size(256),
    ])->assertRedirect(route('member.profile'));

    $avatar = $user->refresh()->avatar;

    expect($avatar)->toStartWith('storage/member/avatars/');
    Storage::disk('public')->assertExists(str_replace('storage/', '', $avatar));

    $this->actingAs($user)->get(route('member.profile'))
        ->assertOk()
        ->assertSee(asset($avatar), false);

    $this->actingAs($user)->get(route('member.profile.edit'))
        ->assertOk()
        ->assertSee(asset($avatar), false);

    $this->actingAs($user)->get(route('member.dashboard'))
        ->assertOk()
        ->assertSee(asset($avatar), false);
});

test('member profile can update birth date from dd mm yyyy display', function () {
    [$user, $member] = createPortalMember('PG-PORTAL-BIRTH-DISPLAY');

    $this->actingAs($user)->patch(route('member.profile.update'), [
        'name' => 'Andi Portal',
        'email' => 'andi.portal@example.com',
        'phone' => '081234567890',
        'gender' => 'male',
        'birth_date_display' => '15/01/2000',
    ])->assertRedirect(route('member.profile'));

    expect($member->refresh()->birth_date->toDateString())->toBe('2000-01-15');
});

test('member profile can update birth date from separated fields', function () {
    [$user, $member] = createPortalMember('PG-PORTAL-BIRTH-PARTS');

    $this->actingAs($user)->patch(route('member.profile.update'), [
        'name' => 'Andi Portal',
        'email' => 'andi.portal@example.com',
        'phone' => '081234567890',
        'gender' => 'male',
        'birth_day' => '15',
        'birth_month' => '1',
        'birth_year' => '2000',
    ])->assertRedirect(route('member.profile'));

    expect($member->refresh()->birth_date->toDateString())->toBe('2000-01-15');
});

test('member profile rejects invalid separated birth date fields', function () {
    [$user] = createPortalMember('PG-PORTAL-BIRTH-PARTS-INVALID');

    $this->actingAs($user)->from(route('member.profile.edit'))->patch(route('member.profile.update'), [
        'name' => 'Andi Portal',
        'email' => 'andi.portal@example.com',
        'phone' => '081234567890',
        'gender' => 'male',
        'birth_day' => '31',
        'birth_month' => '2',
        'birth_year' => '2000',
    ])->assertRedirect(route('member.profile.edit'))
        ->assertSessionHasErrors('birth_date');
});

test('member profile rejects invalid dd mm yyyy birth date display', function () {
    [$user] = createPortalMember('PG-PORTAL-BIRTH-DISPLAY-INVALID');

    $this->actingAs($user)->from(route('member.profile.edit'))->patch(route('member.profile.update'), [
        'name' => 'Andi Portal',
        'email' => 'andi.portal@example.com',
        'phone' => '081234567890',
        'gender' => 'male',
        'birth_date_display' => '31/02/2000',
    ])->assertRedirect(route('member.profile.edit'))
        ->assertSessionHasErrors('birth_date');
});

test('member profile avatar validation rejects oversized image', function () {
    Storage::fake('public');

    [$user] = createPortalMember('PG-PORTAL-AVATAR-INVALID');

    $this->actingAs($user)->from(route('member.profile.edit'))->patch(route('member.profile.update'), [
        'name' => 'Andi Portal',
        'email' => 'andi.portal@example.com',
        'phone' => '081234567890',
        'gender' => 'male',
        'birth_date' => '2000-01-01',
        'avatar' => UploadedFile::fake()->image('avatar.jpg', 256, 256)->size(3072),
    ])->assertRedirect(route('member.profile.edit'))
        ->assertSessionHasErrors('avatar');

    expect(Storage::disk('public')->allFiles('member/avatars'))->toBe([]);
});

test('member profile upload cleans new avatar if profile update fails', function () {
    Storage::fake('public');

    [$user, $member] = createPortalMember('PG-PORTAL-AVATAR-ROLLBACK');
    User::factory()->create(['email' => 'taken-avatar@example.com']);

    $thrown = false;

    try {
        app(UpdateMemberProfileAction::class)->execute($user, $member, [
            'name' => 'Andi Portal',
            'email' => 'taken-avatar@example.com',
            'phone' => '081234567890',
            'gender' => 'male',
            'birth_date' => '2000-01-01',
            'avatar' => UploadedFile::fake()->image('avatar.jpg', 256, 256)->size(256),
        ]);
    } catch (Throwable) {
        $thrown = true;
        // Expected: duplicate email causes the profile transaction to fail.
    }

    expect($thrown)->toBeTrue()
        ->and($user->refresh()->avatar)->toBeNull()
        ->and(Storage::disk('public')->allFiles('member/avatars'))->toBe([]);
});

test('member profile upload can replace oauth avatar url safely', function () {
    Storage::fake('public');

    [$user] = createPortalMember('PG-PORTAL-AVATAR-OAUTH');
    $user->forceFill(['avatar' => 'https://lh3.googleusercontent.com/avatar.png'])->save();

    $this->actingAs($user)->get(route('member.dashboard'))
        ->assertOk()
        ->assertSee('https://lh3.googleusercontent.com/avatar.png', false);

    $this->actingAs($user)->patch(route('member.profile.update'), [
        'name' => 'Andi Avatar',
        'email' => 'andi.portal@example.com',
        'phone' => '081234567890',
        'gender' => 'male',
        'birth_date' => '2000-01-01',
        'avatar' => UploadedFile::fake()->image('avatar.webp', 256, 256)->size(256),
    ])->assertRedirect(route('member.profile'));

    $avatar = $user->refresh()->avatar;

    expect($avatar)->toStartWith('storage/member/avatars/')
        ->and(Storage::disk('public')->allFiles('member/avatars'))->toHaveCount(1);
});

test('inactive qr page shows unavailable state instead of large scannable qr placeholder', function () {
    [$user] = createPortalMember('PG-PORTAL-QR-INACTIVE-STATE');

    $this->actingAs($user)->get(route('member.qr'))
        ->assertOk()
        ->assertSee('data-qr-member-layout', false)
        ->assertSee('data-qr-member-visual', false)
        ->assertSee('data-qr-member-status', false)
        ->assertSee('lg:grid-cols-[minmax(0,0.95fr)_minmax(0,1.05fr)]', false)
        ->assertSee('QR belum aktif')
        ->assertSee('Pilih Membership')
        ->assertSee('Admin hanya dapat scan QR setelah status member aktif')
        ->assertSee('Riwayat Check-in')
        ->assertSee('Belum ada check-in')
        ->assertDontSee('Aktivitas masuk gym terbaru')
        ->assertDontSee('lg:grid-cols-[minmax(0,22rem)_minmax(0,1fr)]', false)
        ->assertDontSee('h-40 w-40')
        ->assertDontSee('QR aktif dan dapat dipindai admin');
});

test('qr page renders recent check ins as a compact table', function () {
    [$user, $member] = createPortalMember('PG-PORTAL-QR-CHECKINS');

    $membershipPackage = ServicePackage::create([
        'name' => 'Gym QR Check-in Test',
        'slug' => 'gym-qr-check-in-test',
        'package_kind' => 'membership',
        'type' => 'gym',
        'category' => 'umum',
        'price' => 250000,
        'duration_days' => 30,
        'is_active' => true,
    ]);

    $membership = Membership::create([
        'member_id' => $member->id,
        'package_id' => $membershipPackage->id,
        'code' => 'MBR-QR-CHECKIN-0001',
        'start_date' => now()->subMonth()->toDateString(),
        'end_date' => now()->addMonth()->toDateString(),
        'price' => 250000,
        'status' => 'active',
    ]);

    $sessionPackage = ServicePackage::create([
        'name' => 'Muaythai QR Session Test',
        'slug' => 'muaythai-qr-session-test',
        'package_kind' => 'session',
        'type' => 'muaythai',
        'category' => 'umum',
        'price' => 350000,
        'session_count' => 4,
        'is_active' => true,
    ]);

    $session = MemberPackageSession::create([
        'member_id' => $member->id,
        'package_id' => $sessionPackage->id,
        'code' => 'MPS-QR-CHECKIN-0001',
        'total_sessions' => 4,
        'used_sessions' => 2,
        'remaining_sessions' => 2,
        'price' => 350000,
        'started_at' => now()->subWeek()->toDateString(),
        'expired_at' => now()->addMonth()->toDateString(),
        'status' => 'active',
    ]);

    $membershipOnlyCheckIn = GymCheckIn::create([
        'member_id' => $member->id,
        'membership_id' => $membership->id,
        'check_in_date' => now()->subDays(2)->toDateString(),
        'check_in_at' => now()->subDays(2)->setTime(8, 30),
        'method' => 'qr',
    ]);

    $checkInWithSession = GymCheckIn::create([
        'member_id' => $member->id,
        'membership_id' => $membership->id,
        'check_in_date' => now()->subDay()->toDateString(),
        'check_in_at' => now()->subDay()->setTime(18, 15),
        'method' => 'qr',
    ]);

    MemberPackageSessionUsage::create([
        'member_package_session_id' => $session->id,
        'member_id' => $member->id,
        'gym_check_in_id' => $checkInWithSession->id,
        'usage_date' => now()->subDay()->toDateString(),
        'used_at' => now()->subDay()->setTime(18, 15),
        'method' => 'admin_qr',
    ]);

    MemberPackageSessionUsage::create([
        'member_package_session_id' => $session->id,
        'member_id' => $member->id,
        'gym_check_in_id' => null,
        'usage_date' => now()->toDateString(),
        'used_at' => now()->setTime(19, 0),
        'method' => 'admin',
        'notes' => 'Standalone sesi tidak masuk tabel check-in.',
    ]);

    [$otherUser, $otherMember] = createPortalMember('PG-PORTAL-QR-OTHER', 'other.qr.checkin@example.com', '081234567891');
    $otherPackage = ServicePackage::create([
        'name' => 'Other QR Membership Test',
        'slug' => 'other-qr-membership-test',
        'package_kind' => 'membership',
        'type' => 'gym',
        'category' => 'umum',
        'price' => 250000,
        'duration_days' => 30,
        'is_active' => true,
    ]);
    $otherMembership = Membership::create([
        'member_id' => $otherMember->id,
        'package_id' => $otherPackage->id,
        'code' => 'MBR-QR-OTHER-0001',
        'start_date' => now()->subMonth()->toDateString(),
        'end_date' => now()->addMonth()->toDateString(),
        'price' => 250000,
        'status' => 'active',
    ]);
    GymCheckIn::create([
        'member_id' => $otherMember->id,
        'membership_id' => $otherMembership->id,
        'check_in_date' => now()->toDateString(),
        'check_in_at' => now()->setTime(9, 0),
        'method' => 'qr',
    ]);

    $response = $this->actingAs($user)->get(route('member.qr'));

    $response->assertOk()
        ->assertSee('Riwayat Check-in')
        ->assertDontSee('Aktivitas masuk gym terbaru')
        ->assertSee('Tanggal')
        ->assertSee('Jam')
        ->assertSee('Paket')
        ->assertDontSee('Paket Membership')
        ->assertSee('Status')
        ->assertSee('Sisa')
        ->assertSee('Gym QR Check-in Test')
        ->assertSee('Muaythai QR Session Test')
        ->assertSee('Check-in')
        ->assertSee('Check-in + Sesi')
        ->assertSee('Sesi')
        ->assertSee('2 sesi')
        ->assertSee('08:30')
        ->assertSee('18:15')
        ->assertSee('19:00')
        ->assertDontSee('Belum ada check-in')
        ->assertDontSee('Standalone sesi tidak masuk tabel check-in.')
        ->assertDontSee('Other QR Membership Test');

    expect($response->getContent())
        ->toContain('member-table-wrap')
        ->toContain('member-status-pill member-status-info">Sesi</span>')
        ->and($membershipOnlyCheckIn->exists)->toBeTrue()
        ->and($otherUser->exists)->toBeTrue();
});

test('active member can download own qr as png attachment', function () {
    [$user, $member] = createPortalMember('PG-PORTAL-QR-DOWNLOAD');
    $user->forceFill(['name' => 'Budi Santoso'])->save();

    $package = ServicePackage::create([
        'name' => 'Gym QR Download Test',
        'slug' => 'gym-qr-download-test',
        'package_kind' => 'membership',
        'type' => 'gym',
        'price' => 250000,
        'duration_days' => 30,
        'is_active' => true,
    ]);

    Membership::create([
        'member_id' => $member->id,
        'package_id' => $package->id,
        'code' => 'MBR-QR-DOWNLOAD-0001',
        'start_date' => now()->subDay()->toDateString(),
        'end_date' => now()->addMonth()->toDateString(),
        'price' => 250000,
        'status' => 'active',
    ]);

    $token = Str::random(64);
    QrToken::create([
        'tokenable_type' => Member::class,
        'tokenable_id' => $member->id,
        'token' => $token,
        'purpose' => 'member',
        'expires_at' => now()->addMonth(),
    ]);

    $this->actingAs($user)->get(route('member.qr'))
        ->assertOk()
        ->assertSee('Download QR')
        ->assertDontSee($token);

    $response = $this->actingAs($user)->get(route('member.qr.download'));

    $response->assertOk()
        ->assertHeader('content-type', 'image/png')
        ->assertHeader('content-disposition', 'attachment; filename="qr-member-budi-santoso.png"');

    expect(substr($response->getContent(), 0, 8))->toBe("\x89PNG\r\n\x1a\n")
        ->and($response->getContent())->not->toContain($token);
});
