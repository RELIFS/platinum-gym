<?php

use App\Models\ClassEnrollment;
use App\Models\ClassSchedule;
use App\Models\GymClass;
use App\Models\Membership;
use App\Models\Package as ServicePackage;
use App\Models\Payment;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Feature\Owner\Support\OwnerPortalFixtures as OwnerFixtures;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

test('owner portal routes require authentication', function (string $path) {
    $this->get($path)->assertRedirect('/login');
})->with([
    '/owner',
    '/owner/laporan',
    '/owner/laporan/keuangan',
    '/owner/laporan/member',
    '/owner/laporan/booking-kelas',
    '/owner/laporan/export',
]);

test('non owner roles cannot access owner portal', function (string $role) {
    $user = User::factory()->create();
    $user->assignRole($role);

    $this->actingAs($user)->get('/owner')->assertForbidden();
})->with(['member', 'admin']);

test('owner profile uses owner account shell and copy', function () {
    $owner = OwnerFixtures::owner(['name' => 'Owner Portal', 'email' => 'owner.portal@example.com']);

    $response = $this->actingAs($owner)->get(route('profile.edit'))
        ->assertOk()
        ->assertSee('Keamanan Akun Owner')
        ->assertSee('Kelola nama, email, dan kata sandi akun owner.')
        ->assertSee('Foto Profil Owner')
        ->assertSee('name="avatar"', false)
        ->assertSee(route('owner.profile-photo.update'), false)
        ->assertSee(asset('images/owner/owner-avatar-default.webp'), false)
        ->assertSee('Navigasi owner')
        ->assertSee('Kembali ke Dashboard')
        ->assertDontSee('Keamanan Akun Admin')
        ->assertDontSee('Profil Admin');

    OwnerFixtures::assertNavigationActive($response, 'profile.edit');
});

test('owner can upload profile photo from owner profile page', function () {
    Storage::fake('public');

    $owner = OwnerFixtures::owner(['name' => 'Owner Portal', 'email' => 'owner.portal@example.com']);

    $this->actingAs($owner)->from(route('profile.edit'))->patch(route('owner.profile-photo.update'), [
        'avatar' => UploadedFile::fake()->image('owner-avatar.jpg', 256, 256)->size(256),
    ])->assertRedirect(route('profile.edit'))
        ->assertSessionHas('status', 'owner-photo-updated');

    $avatar = $owner->refresh()->avatar;

    expect($avatar)->toStartWith('storage/owner/avatars/');
    Storage::disk('public')->assertExists(str_replace('storage/', '', $avatar));

    $this->actingAs($owner)->get(route('profile.edit'))
        ->assertOk()
        ->assertSee(asset($avatar), false)
        ->assertSee('Foto profil owner berhasil diperbarui.');
});

test('owner profile photo upload replaces only local owner avatars', function () {
    Storage::fake('public');

    $owner = OwnerFixtures::owner(['name' => 'Owner Portal', 'email' => 'owner.portal@example.com']);
    Storage::disk('public')->put('owner/avatars/old-avatar.jpg', 'old-avatar');
    $owner->forceFill(['avatar' => 'storage/owner/avatars/old-avatar.jpg'])->save();

    $this->actingAs($owner)->patch(route('owner.profile-photo.update'), [
        'avatar' => UploadedFile::fake()->image('owner-avatar.png', 256, 256)->size(256),
    ])->assertRedirect(route('profile.edit'));

    Storage::disk('public')->assertMissing('owner/avatars/old-avatar.jpg');

    $avatar = $owner->refresh()->avatar;
    expect($avatar)->toStartWith('storage/owner/avatars/');
    Storage::disk('public')->assertExists(str_replace('storage/', '', $avatar));

    $owner->forceFill(['avatar' => 'https://example.com/owner-avatar.png'])->save();

    $this->actingAs($owner)->patch(route('owner.profile-photo.update'), [
        'avatar' => UploadedFile::fake()->image('owner-avatar.webp', 256, 256)->size(256),
    ])->assertRedirect(route('profile.edit'));

    expect($owner->refresh()->avatar)->toStartWith('storage/owner/avatars/');
});

test('owner profile photo validation rejects invalid uploads', function () {
    Storage::fake('public');

    $owner = OwnerFixtures::owner(['name' => 'Owner Portal', 'email' => 'owner.portal@example.com']);

    $this->actingAs($owner)->from(route('profile.edit'))->patch(route('owner.profile-photo.update'), [
        'avatar' => UploadedFile::fake()->image('owner-avatar.jpg', 256, 256)->size(3072),
    ])->assertRedirect(route('profile.edit'))
        ->assertSessionHasErrors('avatar');

    $this->actingAs($owner)->from(route('profile.edit'))->patch(route('owner.profile-photo.update'), [
        'avatar' => UploadedFile::fake()->create('owner-avatar.svg', 12, 'image/svg+xml'),
    ])->assertRedirect(route('profile.edit'))
        ->assertSessionHasErrors('avatar');

    expect(Storage::disk('public')->allFiles('owner/avatars'))->toBe([]);
});

test('owner profile photo upload route is owner only', function (string $role) {
    $user = User::factory()->create();
    $user->assignRole($role);

    $this->actingAs($user)->patch(route('owner.profile-photo.update'), [
        'avatar' => UploadedFile::fake()->image('owner-avatar.jpg', 256, 256)->size(256),
    ])->assertForbidden();
})->with(['member', 'admin']);

test('owner sidebar marks only the current owner page as active', function (string $path, string $route) {
    $owner = OwnerFixtures::owner(['name' => 'Owner Portal', 'email' => 'owner.portal@example.com']);
    $response = $this->actingAs($owner)->get($path)->assertOk();

    OwnerFixtures::assertNavigationActive($response, $route);
})->with([
    ['/owner', 'owner.dashboard'],
    ['/owner/laporan', 'owner.reports.index'],
    ['/owner/laporan/keuangan', 'owner.reports.finance'],
    ['/owner/laporan/member', 'owner.reports.members'],
    ['/owner/laporan/booking-kelas', 'owner.reports.classes'],
]);

test('owner mobile drawer renders identity and logout footer', function () {
    $owner = OwnerFixtures::owner(['name' => 'Owner Portal', 'email' => 'owner.portal@example.com']);
    $response = $this->actingAs($owner)->get(route('owner.reports.finance'))->assertOk();
    $content = $response->getContent();

    $response
        ->assertSee('aria-label="Identitas owner"', false)
        ->assertSee('aria-label="Identitas owner mobile"', false)
        ->assertSee('data-owner-sidebar-logout="desktop"', false)
        ->assertSee('data-owner-sidebar-logout="mobile"', false)
        ->assertSee('Owner Portal');

    expect(substr_count($content, 'data-owner-sidebar-logout='))->toBe(2);
    expect(substr_count($content, '>Keluar</button>'))->toBe(2);
});

test('owner invoice pages keep financial navigation context active', function (string $routeName) {
    $owner = OwnerFixtures::owner(['name' => 'Owner Portal', 'email' => 'owner.portal@example.com']);
    [, $member] = OwnerFixtures::member('PG-OWNER-INVOICE');
    $invoice = OwnerFixtures::invoiceForMember($member);

    $response = $this->actingAs($owner)->get(route($routeName, $invoice))->assertOk();

    OwnerFixtures::assertNavigationActive($response, 'owner.reports.finance');

    if ($routeName === 'owner.invoices.receipt') {
        $response->assertSee('Kembali ke Invoice');
    }
})->with([
    'owner.invoices.show',
    'owner.invoices.receipt',
]);

test('owner report filter and reset links stay on the current report page', function (string $route) {
    $owner = OwnerFixtures::owner(['name' => 'Owner Portal', 'email' => 'owner.portal@example.com']);
    $url = route($route);

    $this->actingAs($owner)->get($url)
        ->assertOk()
        ->assertSee('Pusat Laporan')
        ->assertSee('action="'.$url.'"', false)
        ->assertSee('href="'.$url.'" class="owner-button-secondary">Reset', false)
        ->assertSee(route('owner.reports.export'), false)
        ->assertSee('format=xlsx', false)
        ->assertSee('format=pdf', false);
})->with([
    'owner.reports.index',
    'owner.reports.finance',
    'owner.reports.members',
    'owner.reports.classes',
]);

test('owner dashboard renders real business monitoring data', function () {
    $owner = OwnerFixtures::owner(['name' => 'Owner Portal', 'email' => 'owner.portal@example.com']);
    [, $member] = OwnerFixtures::member('PG-OWNER-DATA');

    $package = ServicePackage::create([
        'name' => 'Membership Owner Test',
        'slug' => 'membership-owner-test',
        'package_kind' => 'membership',
        'type' => 'gym',
        'price' => 250000,
        'duration_days' => 30,
        'is_active' => true,
    ]);

    $membership = Membership::create([
        'member_id' => $member->id,
        'package_id' => $package->id,
        'code' => 'MBR-OWNER-0001',
        'start_date' => now()->subDay()->toDateString(),
        'end_date' => now()->addMonth()->toDateString(),
        'price' => 250000,
        'status' => 'active',
    ]);

    Payment::create([
        'payment_code' => 'PAY-OWNER-PAID',
        'member_id' => $member->id,
        'payable_type' => Membership::class,
        'payable_id' => $membership->id,
        'method' => 'cash',
        'amount' => 250000,
        'status' => 'paid',
        'paid_at' => now()->subDay(),
        'midtrans_snap_token' => 'secret-snap-token',
        'midtrans_raw_response' => ['secret' => 'raw-response'],
    ]);

    Payment::create([
        'payment_code' => 'PAY-OWNER-PENDING',
        'member_id' => $member->id,
        'payable_type' => Membership::class,
        'payable_id' => $membership->id,
        'method' => 'midtrans',
        'amount' => 500000,
        'status' => 'waiting_confirmation',
    ]);

    $gymClass = GymClass::create([
        'name' => 'Yoga Owner',
        'slug' => 'yoga-owner',
        'class_type' => 'yoga',
        'access_type' => 'membership',
        'capacity' => 20,
        'is_active' => true,
    ]);

    $schedule = ClassSchedule::create([
        'gym_class_id' => $gymClass->id,
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

    $this->actingAs($owner)->get('/owner')
        ->assertOk()
        ->assertSee('Dashboard Owner')
        ->assertSee('Ringkasan bisnis')
        ->assertSee('Pendapatan periode ini')
        ->assertSee('Rp 250.000')
        ->assertSee('Transaksi terkonfirmasi')
        ->assertSee('Member aktif')
        ->assertSee('Membership aktif')
        ->assertSee('Booking periode ini')
        ->assertSee('Tren pendapatan')
        ->assertSee('id="owner-business-trend-chart"', false)
        ->assertSee('id="owner-business-trend-data"', false)
        ->assertSee('PAY-OWNER-PAID')
        ->assertDontSee('Rp 750.000')
        ->assertDontSee('secret-snap-token')
        ->assertDontSee('raw-response')
        ->assertDontSee('Laba bersih');
});

test('owner reports filter data and export csv without pending revenue', function () {
    $owner = OwnerFixtures::owner(['name' => 'Owner Portal', 'email' => 'owner.portal@example.com']);
    [, $member] = OwnerFixtures::member('PG-OWNER-REPORT');

    $package = ServicePackage::create([
        'name' => 'Report Package',
        'slug' => 'report-package',
        'package_kind' => 'membership',
        'type' => 'gym',
        'price' => 200000,
        'duration_days' => 30,
        'is_active' => true,
    ]);

    $membership = Membership::create([
        'member_id' => $member->id,
        'package_id' => $package->id,
        'code' => 'MBR-OWNER-REPORT',
        'start_date' => now()->subDay()->toDateString(),
        'end_date' => now()->addMonth()->toDateString(),
        'price' => 200000,
        'status' => 'active',
    ]);

    Payment::create([
        'payment_code' => 'PAY-OWNER-REPORT-PAID',
        'member_id' => $member->id,
        'payable_type' => Membership::class,
        'payable_id' => $membership->id,
        'method' => 'cash',
        'amount' => 200000,
        'status' => 'paid',
        'paid_at' => now(),
    ]);

    Payment::create([
        'payment_code' => 'PAY-OWNER-REPORT-PENDING',
        'member_id' => $member->id,
        'payable_type' => Membership::class,
        'payable_id' => $membership->id,
        'method' => 'cash',
        'amount' => 900000,
        'status' => 'waiting_confirmation',
    ]);

    $this->actingAs($owner)->get(route('owner.reports.finance', [
        'date_from' => now()->startOfMonth()->toDateString(),
        'date_to' => now()->toDateString(),
    ]))
        ->assertOk()
        ->assertSee('Laporan Keuangan')
        ->assertSee('PAY-OWNER-REPORT-PAID')
        ->assertSee('Rp 200.000')
        ->assertDontSee('PAY-OWNER-REPORT-PENDING')
        ->assertDontSee('Rp 1.100.000')
        ->assertSee('Unduh CSV')
        ->assertSee('Unduh Excel')
        ->assertSee('Unduh PDF');

    $response = $this->actingAs($owner)->get(route('owner.reports.export', [
        'report_type' => 'finance',
        'date_from' => now()->startOfMonth()->toDateString(),
        'date_to' => now()->toDateString(),
    ]));

    $response->assertOk()
        ->assertHeader('content-type', 'text/csv; charset=UTF-8');

    expect($response->streamedContent())
        ->toContain('PAY-OWNER-REPORT-PAID')
        ->not->toContain('PAY-OWNER-REPORT-PENDING');

    $xlsx = $this->actingAs($owner)->get(route('owner.reports.export', [
        'report_type' => 'finance',
        'format' => 'xlsx',
        'date_from' => now()->startOfMonth()->toDateString(),
        'date_to' => now()->toDateString(),
    ]));

    $xlsx->assertOk();
    expect((string) $xlsx->headers->get('content-disposition'))->toContain('.xlsx');

    $pdf = $this->actingAs($owner)->get(route('owner.reports.export', [
        'report_type' => 'finance',
        'format' => 'pdf',
        'date_from' => now()->startOfMonth()->toDateString(),
        'date_to' => now()->toDateString(),
    ]));

    $pdf->assertOk();
    expect((string) $pdf->headers->get('content-disposition'))->toContain('.pdf');
});
