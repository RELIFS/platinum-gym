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
use App\Models\QrToken;
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

test('admin can access all admin pages', function (string $path, string $title) {
    $admin = createAdminPortalUser();

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
        ->assertSee('Zumba Admin')
        ->assertSee('Check-in Hari Ini');

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

test('admin can approve and reject payments', function () {
    $admin = createAdminPortalUser();
    [, $member] = createAdminPortalMember('PG-ADMIN-PAYMENT-ACTION');

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
    $admin = createAdminPortalUser();
    [, $member] = createAdminPortalMember('PG-ADMIN-CASH');

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
});

test('admin can update whitelisted public settings without exposing secrets', function () {
    $admin = createAdminPortalUser();

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
        'operational_hours_weekday' => '06:00-22:00',
        'operational_hours_weekend' => '06:00-20:00',
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
        ->assertSee('qr_secret')
        ->assertSee('Tersamarkan')
        ->assertDontSee('do-not-render-this-secret');
});

test('admin can filter reports and export csv', function () {
    $admin = createAdminPortalUser();

    $this->actingAs($admin)->get(route('admin.reports', [
        'date_from' => now()->startOfMonth()->toDateString(),
        'date_to' => now()->toDateString(),
    ]))
        ->assertOk()
        ->assertSee('Periode operasional')
        ->assertSee('Export CSV');

    $response = $this->actingAs($admin)->get(route('admin.reports.export', [
        'date_from' => now()->startOfMonth()->toDateString(),
        'date_to' => now()->toDateString(),
    ]));

    $response->assertOk()
        ->assertHeader('content-type', 'text/csv; charset=UTF-8');

    expect($response->streamedContent())->toContain('Metrik');
});

test('admin can scan active member qr for check in', function () {
    $admin = createAdminPortalUser();
    [, $member] = createAdminPortalMember('PG-ADMIN-CHECKIN');

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

    $qrToken = QrToken::create([
        'tokenable_type' => Member::class,
        'tokenable_id' => $member->id,
        'token' => str_repeat('a', 64),
        'purpose' => 'member',
        'expires_at' => now()->addMonth(),
    ]);

    $this->actingAs($admin)->post(route('admin.check-in.scan'), [
        'token' => $qrToken->token,
    ])->assertRedirect();

    $this->assertDatabaseHas('gym_check_ins', [
        'member_id' => $member->id,
        'membership_id' => $membership->id,
        'method' => 'qr',
        'scanned_by' => $admin->id,
    ]);
});

test('admin can toggle product active status', function () {
    $admin = createAdminPortalUser();

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
    $admin = createAdminPortalUser();

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
    $admin = createAdminPortalUser();

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
    $admin = createAdminPortalUser();

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
    $admin = createAdminPortalUser();

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
