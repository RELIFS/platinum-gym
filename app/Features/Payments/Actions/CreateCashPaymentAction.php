<?php

namespace App\Features\Payments\Actions;

use App\Features\Payments\Support\PaymentCode;
use App\Models\Member;
use App\Models\MemberPackageSession;
use App\Models\Membership;
use App\Models\Package;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class CreateCashPaymentAction
{
    public function __construct(private readonly FulfillPaidPaymentAction $fulfillPaidPayment) {}

    public function handle(Member $member, Package $package, int $adminId, ?int $trainerId = null, ?string $note = null): Payment
    {
        if (! $package->is_active) {
            throw new RuntimeException('Paket tidak aktif. Pilih paket lain sebelum mencatat pembayaran.');
        }

        if ($package->package_kind !== 'membership' && blank($package->session_count)) {
            throw new RuntimeException('Paket sesi belum memiliki jumlah sesi. Lengkapi data paket terlebih dahulu.');
        }

        if ($package->requires_active_membership && ! $this->hasActiveMembership($member)) {
            throw new RuntimeException('Member belum memiliki membership aktif untuk membeli paket ini.');
        }

        $payment = DB::transaction(function () use ($member, $package, $note, $trainerId): Payment {
            $payable = $package->package_kind === 'membership'
                ? $this->createMembership($member, $package)
                : $this->createPackageSession($member, $package, $trainerId);

            return Payment::create([
                'payment_code' => PaymentCode::payment(),
                'member_id' => $member->id,
                'payable_type' => $payable::class,
                'payable_id' => $payable->id,
                'method' => 'cash',
                'amount' => $this->packagePrice($package),
                'status' => 'waiting_confirmation',
                'note' => $note ?: 'Pembayaran tunai dicatat oleh admin.',
            ]);
        });

        return $this->fulfillPaidPayment->handle($payment, $adminId);
    }

    private function createMembership(Member $member, Package $package): Membership
    {
        return Membership::create([
            'member_id' => $member->id,
            'package_id' => $package->id,
            'code' => PaymentCode::membership(),
            'start_date' => null,
            'end_date' => null,
            'price' => $this->packagePrice($package),
            'duration_days_snapshot' => max((int) ($package->effectiveDurationDays() ?? 30), 1),
            'status' => 'pending_payment',
        ]);
    }

    private function createPackageSession(Member $member, Package $package, ?int $trainerId): MemberPackageSession
    {
        $durationDays = $package->duration_days ? max((int) $package->duration_days, 1) : null;

        return MemberPackageSession::create([
            'member_id' => $member->id,
            'package_id' => $package->id,
            'trainer_id' => $trainerId,
            'code' => PaymentCode::packageSession(),
            'total_sessions' => (int) $package->session_count,
            'used_sessions' => 0,
            'remaining_sessions' => (int) $package->session_count,
            'price' => $this->packagePrice($package),
            'started_at' => now()->toDateString(),
            'expired_at' => $durationDays ? now()->addDays($durationDays - 1)->toDateString() : null,
            'status' => 'pending_payment',
        ]);
    }

    private function packagePrice(Package $package): float
    {
        return (float) ($package->promo_price ?? $package->price);
    }

    private function hasActiveMembership(Member $member): bool
    {
        return $member->memberships()
            ->activeForAccess()
            ->exists();
    }
}
