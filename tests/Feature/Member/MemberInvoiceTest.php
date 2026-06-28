<?php

use Database\Seeders\RolePermissionSeeder;
use Tests\Feature\Member\Support\MemberPortalFixtures as MemberFixtures;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

test('member invoice and receipt are restricted to the invoice owner', function () {
    [$user, $member] = MemberFixtures::member('PG-MEMBER-INVOICE-OWN');
    [$otherUser, $otherMember] = MemberFixtures::member('PG-MEMBER-INVOICE-OTHER');

    $invoice = MemberFixtures::invoice(MemberFixtures::sensitivePayment($member), ['invoice_number' => 'INV-MEMBER-OWN']);
    $otherInvoice = MemberFixtures::invoice(MemberFixtures::payment($otherMember), ['invoice_number' => 'INV-MEMBER-OTHER']);

    $this->actingAs($user)->get(route('member.invoices.show', $invoice))
        ->assertOk()
        ->assertSee('Invoice Transaksi')
        ->assertSee('INV-MEMBER-OWN')
        ->assertSee('Lihat Struk')
        ->assertDontSee('secret-snap-token-member')
        ->assertDontSee('payment.example.test/member-secret')
        ->assertDontSee('raw-secret-member')
        ->assertDontSee('Catatan internal member');

    $this->actingAs($user)->get(route('member.invoices.receipt', $invoice))
        ->assertOk()
        ->assertSee('Struk Transaksi')
        ->assertSee('INV-MEMBER-OWN')
        ->assertDontSee('secret-snap-token-member')
        ->assertDontSee('Catatan internal member');

    $this->actingAs($user)->get(route('member.invoices.show', $otherInvoice))->assertForbidden();
    $this->actingAs($otherUser)->get(route('member.invoices.receipt', $invoice))->assertForbidden();
});

test('member invoice download responds as a pdf attachment', function () {
    [$user, $member] = MemberFixtures::member('PG-MEMBER-INVOICE-PDF');
    $invoice = MemberFixtures::invoice(MemberFixtures::payment($member));

    $response = $this->actingAs($user)->get(route('member.invoices.download', $invoice));

    $response->assertOk();
    expect((string) $response->headers->get('content-disposition'))->toContain('.pdf');
});
