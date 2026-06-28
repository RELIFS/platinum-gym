<?php

use App\Models\Invoice;
use App\Models\Member;
use App\Models\Membership;
use App\Models\Package as ServicePackage;
use App\Models\Payment;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

function createInvoiceDocumentMember(string $code, string $email): array
{
    $user = User::factory()->create([
        'name' => 'Invoice Member '.$code,
        'email' => $email,
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

function createInvoiceDocumentFor(Member $member): Invoice
{
    $package = ServicePackage::create([
        'name' => 'Invoice Membership',
        'slug' => 'invoice-membership-'.strtolower($member->member_code),
        'package_kind' => 'membership',
        'type' => 'gym',
        'price' => 300000,
        'duration_days' => 30,
        'is_active' => true,
    ]);

    $membership = Membership::create([
        'member_id' => $member->id,
        'package_id' => $package->id,
        'code' => 'MBR-'.$member->id,
        'start_date' => now()->subDay()->toDateString(),
        'end_date' => now()->addMonth()->toDateString(),
        'price' => 300000,
        'status' => 'active',
    ]);

    $payment = Payment::create([
        'payment_code' => 'PAY-INVOICE-'.$member->id,
        'member_id' => $member->id,
        'payable_type' => Membership::class,
        'payable_id' => $membership->id,
        'method' => 'midtrans',
        'amount' => 300000,
        'status' => 'paid',
        'paid_at' => now(),
        'midtrans_snap_token' => 'secret-snap-token-'.$member->id,
        'midtrans_redirect_url' => 'https://payment.example.test/secret-'.$member->id,
        'midtrans_raw_response' => ['token' => 'raw-secret-'.$member->id],
        'note' => 'Catatan internal pembayaran',
    ]);

    return Invoice::create([
        'payment_id' => $payment->id,
        'invoice_number' => 'INV-INVOICE-'.$member->id,
        'issued_at' => now()->toDateString(),
        'due_date' => now()->addDay()->toDateString(),
        'subtotal' => 300000,
        'discount' => 0,
        'tax' => 0,
        'total' => 300000,
        'status' => 'paid',
    ]);
}

test('member can view only own invoice document', function () {
    [$memberUser, $member] = createInvoiceDocumentMember('PG-INV-0001', 'invoice.member@example.com');
    [$otherUser, $otherMember] = createInvoiceDocumentMember('PG-INV-0002', 'invoice.other@example.com');

    $invoice = createInvoiceDocumentFor($member);
    $otherInvoice = createInvoiceDocumentFor($otherMember);

    $this->actingAs($memberUser)->get(route('member.invoices.show', $invoice))
        ->assertOk()
        ->assertSee('Invoice Transaksi')
        ->assertSee($invoice->invoice_number)
        ->assertSee('PAY-INVOICE-'.$member->id)
        ->assertSee('Invoice Membership')
        ->assertSee('Rp 300.000')
        ->assertSee('Lihat Struk')
        ->assertSee('Unduh PDF')
        ->assertSee('Cetak')
        ->assertDontSee('secret-snap-token')
        ->assertDontSee('payment.example.test')
        ->assertDontSee('raw-secret')
        ->assertDontSee('Catatan internal pembayaran')
        ->assertDontSee($memberUser->phone);

    $this->actingAs($memberUser)->get(route('member.invoices.show', $otherInvoice))->assertForbidden();
    $this->actingAs($otherUser)->get(route('member.invoices.show', $invoice))->assertForbidden();

    $this->actingAs($memberUser)->get(route('member.invoices.receipt', $invoice))
        ->assertOk()
        ->assertSee('Struk Transaksi')
        ->assertSee($invoice->invoice_number)
        ->assertSee('PAY-INVOICE-'.$member->id)
        ->assertSee('Simpan struk ini sebagai bukti transaksi.')
        ->assertDontSee('secret-snap-token')
        ->assertDontSee('payment.example.test')
        ->assertDontSee('raw-secret')
        ->assertDontSee('Catatan internal pembayaran');

    $invoicePdf = $this->actingAs($memberUser)->get(route('member.invoices.download', $invoice));
    $invoicePdf->assertOk();
    expect((string) $invoicePdf->headers->get('content-disposition'))->toContain('.pdf');

    $receiptPdf = $this->actingAs($memberUser)->get(route('member.invoices.download', ['invoice' => $invoice, 'type' => 'receipt']));
    $receiptPdf->assertOk();
    expect((string) $receiptPdf->headers->get('content-disposition'))->toContain('.pdf');
});

test('owner can view invoice document read only', function () {
    $owner = User::factory()->create();
    $owner->assignRole('owner');

    [, $member] = createInvoiceDocumentMember('PG-INV-OWNER', 'invoice.owner.member@example.com');
    $invoice = createInvoiceDocumentFor($member);

    $this->actingAs($owner)->get(route('owner.invoices.show', $invoice))
        ->assertOk()
        ->assertSee('Invoice Transaksi')
        ->assertSee($invoice->invoice_number)
        ->assertSee('Owner')
        ->assertSee('Lihat Struk')
        ->assertDontSee('secret-snap-token')
        ->assertDontSee('raw-secret')
        ->assertDontSee('Ubah')
        ->assertDontSee('Hapus');

    $this->actingAs($owner)->get(route('owner.invoices.receipt', $invoice))
        ->assertOk()
        ->assertSee('Struk Transaksi')
        ->assertSee($invoice->invoice_number)
        ->assertDontSee('secret-snap-token')
        ->assertDontSee('raw-secret');

    $response = $this->actingAs($owner)->get(route('owner.invoices.download', $invoice));
    $response->assertOk();
    expect((string) $response->headers->get('content-disposition'))->toContain('.pdf');
});
