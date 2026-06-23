<?php

use App\Models\Invoice;
use App\Models\Payment;
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
    $membership = AdminFixture::membership($member, overrides: ['status' => 'pending_payment']);
    $payment = AdminFixture::payment($member, $membership, ['status' => 'waiting_confirmation']);

    $this->actingAs($admin)
        ->post(route('admin.payments.approve', $payment))
        ->assertRedirect()
        ->assertSessionHas('status', 'Pembayaran berhasil disetujui dan layanan member diperbarui.');

    expect($payment->refresh()->status)->toBe('paid')
        ->and($membership->refresh()->status)->toBe('active')
        ->and(Invoice::query()->where('payment_id', $payment->id)->where('status', 'paid')->exists())->toBeTrue();
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
    $package = AdminFixture::package(['name' => 'Cash Membership Admin QA']);

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
        ->and(Invoice::query()->where('payment_id', $payment->id)->exists())->toBeTrue();
});
