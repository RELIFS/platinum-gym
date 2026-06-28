<?php

use App\Models\Invoice;
use App\Models\MemberPackageSession;
use App\Models\Payment;
use App\Notifications\Payments\PaymentRejectedNotification;
use App\Notifications\Payments\PaymentSucceededNotification;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Support\Facades\Notification;
use Tests\Feature\Admin\Support\AdminPortalFixtures as AdminFixture;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

test('admin payment page does not expose gateway tokens or raw response payloads', function () {
    $admin = AdminFixture::admin();
    [, $member] = AdminFixture::member();
    $membership = AdminFixture::membership($member, overrides: ['status' => 'pending_payment']);

    AdminFixture::payment($member, $membership, [
        'payment_code' => 'PAY-ADM-SENSITIVE',
        'midtrans_snap_token' => 'secret-snap-token-admin',
        'midtrans_redirect_url' => 'https://payment.example.test/secret-redirect',
        'midtrans_raw_response' => ['secret' => 'raw-gateway-secret'],
    ]);

    $this->actingAs($admin)
        ->get('/admin/pembayaran')
        ->assertOk()
        ->assertSee('PAY-ADM-SENSITIVE')
        ->assertDontSee('secret-snap-token-admin')
        ->assertDontSee('secret-redirect')
        ->assertDontSee('raw-gateway-secret');
});

test('admin can approve waiting payment and activate membership service', function () {
    Notification::fake();
    $admin = AdminFixture::admin();
    [, $member] = AdminFixture::member();
    $membership = AdminFixture::membership($member, overrides: [
        'status' => 'pending_payment',
        'start_date' => null,
        'end_date' => null,
    ]);
    $payment = AdminFixture::payment($member, $membership, ['status' => 'waiting_confirmation']);

    $this->actingAs($admin)
        ->post(route('admin.payments.approve', $payment))
        ->assertRedirect()
        ->assertSessionHas('status', 'Pembayaran berhasil disetujui dan layanan member diperbarui.');

    expect($payment->refresh()->status)->toBe('paid')
        ->and($membership->refresh()->status)->toBe('active')
        ->and($membership->start_date)->toBeNull()
        ->and($membership->end_date)->toBeNull()
        ->and($membership->activated_at)->not->toBeNull()
        ->and(Invoice::query()->where('payment_id', $payment->id)->where('status', 'paid')->exists())->toBeTrue();

    Notification::assertSentTo($member->user, PaymentSucceededNotification::class, function (PaymentSucceededNotification $notification) use ($payment, $member): bool {
        $rendered = $notification->toMail($member->user)->render();

        return $notification->payment->is($payment)
            && str_contains($rendered, $payment->payment_code)
            && str_contains($rendered, 'masa aktif mulai saat check-in pertama');
    });
});

test('admin payment approval preserves legacy pending membership dates', function () {
    Notification::fake();
    $admin = AdminFixture::admin();
    [, $member] = AdminFixture::member();
    $legacyStartDate = now()->subDay()->toDateString();
    $legacyEndDate = now()->addMonth()->toDateString();
    $membership = AdminFixture::membership($member, overrides: [
        'status' => 'pending_payment',
        'start_date' => $legacyStartDate,
        'end_date' => $legacyEndDate,
    ]);
    $payment = AdminFixture::payment($member, $membership, ['status' => 'waiting_confirmation']);

    $this->actingAs($admin)
        ->post(route('admin.payments.approve', $payment))
        ->assertRedirect()
        ->assertSessionHas('status', 'Pembayaran berhasil disetujui dan layanan member diperbarui.');

    expect($membership->refresh()->status)->toBe('active')
        ->and($membership->start_date->toDateString())->toBe($legacyStartDate)
        ->and($membership->end_date->toDateString())->toBe($legacyEndDate)
        ->and($membership->activated_at)->not->toBeNull();
});

test('admin can reject unpaid payment without mutating paid payments', function () {
    Notification::fake();
    $admin = AdminFixture::admin();
    [, $member] = AdminFixture::member();
    $membership = AdminFixture::membership($member, overrides: ['status' => 'pending_payment']);
    $payment = AdminFixture::payment($member, $membership, ['status' => 'waiting_payment']);
    $invoice = AdminFixture::invoice($payment);

    $this->actingAs($admin)
        ->post(route('admin.payments.reject', $payment), ['reason' => 'Bukti pembayaran belum sesuai.'])
        ->assertRedirect()
        ->assertSessionHas('status', 'Pembayaran berhasil ditolak.');

    expect($payment->refresh()->status)->toBe('rejected')
        ->and($payment->rejected_reason)->toBe('Bukti pembayaran belum sesuai.')
        ->and($membership->refresh()->status)->toBe('cancelled')
        ->and($invoice->refresh()->status)->toBe('rejected');

    Notification::assertSentTo($member->user, PaymentRejectedNotification::class, function (PaymentRejectedNotification $notification) use ($payment, $member): bool {
        $rendered = $notification->toMail($member->user)->render();

        return $notification->payment->is($payment)
            && str_contains($rendered, $payment->payment_code)
            && str_contains($rendered, 'Bukti pembayaran belum sesuai.');
    });

    $paidPayment = AdminFixture::payment($member, $membership, ['status' => 'paid']);

    $this->actingAs($admin)
        ->post(route('admin.payments.reject', $paidPayment), ['reason' => 'Tidak boleh mengubah paid.'])
        ->assertRedirect();

    expect($paidPayment->refresh()->status)->toBe('paid')
        ->and($paidPayment->rejected_reason)->toBeNull();
});

test('admin can record cash payment and service is fulfilled', function () {
    Notification::fake();
    $admin = AdminFixture::admin();
    [, $member] = AdminFixture::member();
    $package = AdminFixture::package([
        'name' => 'Cash Membership Admin QA',
        'base_duration_days' => 180,
        'bonus_duration_days' => 60,
        'bonus_label' => 'Gratis 2 bulan',
        'duration_days' => 240,
    ]);

    $this->actingAs($admin)
        ->post(route('admin.payments.cash'), [
            'member_id' => $member->id,
            'package_id' => $package->id,
            'note' => 'Dibayar tunai di kasir.',
        ])
        ->assertRedirect()
        ->assertSessionHas('status');

    $payment = Payment::query()->where('member_id', $member->id)->where('method', 'cash')->latest()->firstOrFail();

    expect($payment->status)->toBe('paid')
        ->and($payment->payable->status)->toBe('active')
        ->and($payment->payable->start_date)->toBeNull()
        ->and($payment->payable->end_date)->toBeNull()
        ->and($payment->payable->duration_days_snapshot)->toBe(240)
        ->and(Invoice::query()->where('payment_id', $payment->id)->exists())->toBeTrue();
});

test('admin cash payment trainer selection follows selected package', function () {
    Notification::fake();
    $admin = AdminFixture::admin();
    [, $member] = AdminFixture::member();
    $membershipPackage = AdminFixture::package(['name' => 'Cash No Trainer QA']);
    $ptPackage = AdminFixture::package([
        'name' => 'Cash PT Trainer QA',
        'package_kind' => 'personal_trainer',
        'type' => 'pt',
        'duration_days' => null,
        'session_count' => 5,
        'requires_active_membership' => false,
    ]);
    $ptTrainer = AdminFixture::trainer(['name' => 'Coach PT Cash QA', 'specialization' => 'Personal Trainer']);
    $muaythaiTrainer = AdminFixture::trainer(['name' => 'Coach Muaythai Cash QA', 'specialization' => 'Muaythai']);

    $this->actingAs($admin)
        ->get(route('admin.payments'))
        ->assertOk()
        ->assertSee('adminCashPaymentForm', false)
        ->assertSee('x-bind:required="trainerRequired"', false)
        ->assertSee('Trainer wajib sesuai spesialisasi paket yang dipilih.');

    $this->actingAs($admin)
        ->from(route('admin.payments'))
        ->post(route('admin.payments.cash'), [
            'member_id' => $member->id,
            'package_id' => $ptPackage->id,
        ])
        ->assertRedirect(route('admin.payments'))
        ->assertSessionHasErrors(['trainer_id' => 'Pilih trainer yang sesuai dengan paket.']);

    $this->actingAs($admin)
        ->from(route('admin.payments'))
        ->post(route('admin.payments.cash'), [
            'member_id' => $member->id,
            'package_id' => $ptPackage->id,
            'trainer_id' => $muaythaiTrainer->id,
        ])
        ->assertRedirect(route('admin.payments'))
        ->assertSessionHasErrors(['trainer_id' => 'Trainer yang dipilih tidak sesuai dengan paket.']);

    $this->actingAs($admin)
        ->from(route('admin.payments'))
        ->post(route('admin.payments.cash'), [
            'member_id' => $member->id,
            'package_id' => $membershipPackage->id,
            'trainer_id' => $ptTrainer->id,
        ])
        ->assertRedirect(route('admin.payments'))
        ->assertSessionHasErrors(['trainer_id' => 'Trainer hanya dapat dipilih untuk paket Personal Trainer atau Muaythai.']);

    $this->actingAs($admin)
        ->post(route('admin.payments.cash'), [
            'member_id' => $member->id,
            'package_id' => $ptPackage->id,
            'trainer_id' => $ptTrainer->id,
        ])
        ->assertRedirect()
        ->assertSessionHas('status');

    $payment = Payment::query()->where('member_id', $member->id)->where('method', 'cash')->latest()->firstOrFail();

    expect($payment->payable)->toBeInstanceOf(MemberPackageSession::class)
        ->and($payment->payable->trainer_id)->toBe($ptTrainer->id);
});
