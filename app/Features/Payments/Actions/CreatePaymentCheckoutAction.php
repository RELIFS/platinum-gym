<?php

namespace App\Features\Payments\Actions;

use App\Features\Payments\Contracts\PaymentGateway;
use App\Features\Payments\Support\PaymentCode;
use App\Models\ClassEnrollment;
use App\Models\Member;
use App\Models\MemberPackageSession;
use App\Models\Membership;
use App\Models\Package;
use App\Models\Payment;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class CreatePaymentCheckoutAction
{
    public function __construct(
        private readonly PaymentGateway $gateway,
        private readonly CreateInvoiceAction $createInvoice,
    ) {}

    public function membership(Member $member, Package $package): Payment
    {
        if ($package->package_kind !== 'membership' || ! $package->is_active) {
            throw new RuntimeException('Paket membership tidak tersedia.');
        }

        $membership = Membership::create([
            'member_id' => $member->id,
            'package_id' => $package->id,
            'code' => PaymentCode::membership(),
            'start_date' => now()->toDateString(),
            'end_date' => now()->addDays(max((int) ($package->duration_days ?? 30), 1) - 1)->toDateString(),
            'price' => $this->packagePrice($package),
            'status' => 'pending_payment',
        ]);

        return $this->paymentFor($member, $membership, $this->packagePrice($package));
    }

    public function packageSession(Member $member, Package $package, ?int $trainerId = null): Payment
    {
        if ($package->package_kind === 'membership' || ! $package->is_active || blank($package->session_count)) {
            throw new RuntimeException('Paket sesi tidak tersedia.');
        }

        if ($package->requires_active_membership && ! $this->hasActiveMembership($member)) {
            throw new RuntimeException('Paket ini membutuhkan membership aktif.');
        }

        $session = MemberPackageSession::create([
            'member_id' => $member->id,
            'package_id' => $package->id,
            'trainer_id' => $trainerId,
            'code' => PaymentCode::packageSession(),
            'total_sessions' => (int) $package->session_count,
            'used_sessions' => 0,
            'remaining_sessions' => (int) $package->session_count,
            'price' => $this->packagePrice($package),
            'status' => 'pending_payment',
        ]);

        return $this->paymentFor($member, $session, $this->packagePrice($package));
    }

    public function classEnrollment(Member $member, ClassEnrollment $enrollment, int|float $amount): Payment
    {
        return $this->paymentFor($member, $enrollment, $amount);
    }

    private function paymentFor(Member $member, Model $payable, int|float $amount): Payment
    {
        return DB::transaction(function () use ($member, $payable, $amount): Payment {
            $paymentCode = PaymentCode::payment();

            $payment = Payment::create([
                'payment_code' => $paymentCode,
                'member_id' => $member->id,
                'payable_type' => $payable::class,
                'payable_id' => $payable->id,
                'method' => 'midtrans',
                'amount' => $amount,
                'status' => 'waiting_payment',
                'midtrans_order_id' => PaymentCode::midtransOrder($paymentCode),
                'expires_at' => now()->addDay(),
            ]);

            $gateway = $this->gateway->createSnapTransaction($payment);

            $payment->forceFill([
                'midtrans_snap_token' => $gateway['token'],
                'midtrans_redirect_url' => $gateway['redirect_url'],
                'midtrans_raw_response' => $gateway['raw'],
            ])->save();

            $this->createInvoice->handle($payment);

            return $payment->refresh();
        });
    }

    private function packagePrice(Package $package): float
    {
        return (float) ($package->promo_price ?? $package->price);
    }

    private function hasActiveMembership(Member $member): bool
    {
        return $member->memberships()
            ->where('status', 'active')
            ->whereDate('start_date', '<=', now()->toDateString())
            ->whereDate('end_date', '>=', now()->toDateString())
            ->exists();
    }
}
