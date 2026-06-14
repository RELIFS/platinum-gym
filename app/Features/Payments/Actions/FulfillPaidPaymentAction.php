<?php

namespace App\Features\Payments\Actions;

use App\Models\ClassEnrollment;
use App\Models\Member;
use App\Models\MemberPackageSession;
use App\Models\Membership;
use App\Models\Payment;
use App\Models\QrToken;
use App\Notifications\MemberOperationalNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class FulfillPaidPaymentAction
{
    public function __construct(private readonly CreateInvoiceAction $createInvoice) {}

    public function handle(Payment $payment, ?int $verifiedBy = null): Payment
    {
        return DB::transaction(function () use ($payment, $verifiedBy): Payment {
            $payment = Payment::query()->with(['member.user', 'payable'])->lockForUpdate()->findOrFail($payment->id);

            if ($payment->status === 'paid') {
                return $payment;
            }

            $payment->forceFill([
                'status' => 'paid',
                'paid_at' => $payment->paid_at ?? now(),
                'verified_by' => $verifiedBy ?? $payment->verified_by,
                'verified_at' => $payment->verified_at ?? now(),
                'failure_reason' => null,
            ])->save();

            $payable = $payment->payable;

            if ($payable instanceof Membership) {
                $this->activateMembership($payment, $payable, $verifiedBy);
            }

            if ($payable instanceof MemberPackageSession) {
                $this->activatePackageSession($payment, $payable, $verifiedBy);
            }

            if ($payable instanceof ClassEnrollment) {
                $payable->forceFill(['status' => 'booked', 'payment_id' => $payment->id])->save();
            }

            $invoice = $this->createInvoice->handle($payment);
            $invoice->forceFill(['status' => 'paid'])->save();

            $payment->member?->user?->notify(new MemberOperationalNotification(
                'Pembayaran Berhasil',
                'Pembayaran '.$payment->payment_code.' sudah berhasil dan layanan Anda telah diperbarui.',
                route('member.transactions'),
                'Lihat Transaksi',
                true,
            ));

            return $payment->refresh();
        });
    }

    private function activateMembership(Payment $payment, Membership $membership, ?int $verifiedBy): void
    {
        $package = $membership->package;
        $startDate = now()->toDateString();
        $endDate = now()->addDays(max((int) ($package?->duration_days ?? 30), 1) - 1)->toDateString();

        $membership->forceFill([
            'start_date' => $membership->start_date ?? $startDate,
            'end_date' => $membership->end_date && $membership->end_date->isFuture() ? $membership->end_date : $endDate,
            'status' => 'active',
            'approved_by' => $verifiedBy,
            'approved_at' => now(),
        ])->save();

        $this->ensureQrToken($payment->member, $membership);
    }

    private function activatePackageSession(Payment $payment, MemberPackageSession $session, ?int $verifiedBy): void
    {
        $session->forceFill([
            'started_at' => $session->started_at ?? now()->toDateString(),
            'expired_at' => $session->expired_at,
            'status' => 'active',
            'approved_by' => $verifiedBy,
            'approved_at' => now(),
        ])->save();
    }

    private function ensureQrToken(?Member $member, Membership $membership): void
    {
        if (! $member) {
            return;
        }

        QrToken::query()
            ->where('tokenable_type', Member::class)
            ->where('tokenable_id', $member->id)
            ->where('purpose', 'member')
            ->where('is_revoked', false)
            ->update(['is_revoked' => true]);

        QrToken::create([
            'tokenable_type' => Member::class,
            'tokenable_id' => $member->id,
            'token' => hash('sha256', $member->member_code.'|'.Str::random(48).'|'.microtime(true)),
            'purpose' => 'member',
            'expires_at' => $membership->end_date?->endOfDay(),
        ]);
    }
}
