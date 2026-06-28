<?php

namespace App\Http\Controllers\Admin;

use App\Features\Payments\Actions\CreateCashPaymentAction;
use App\Features\Payments\Actions\FulfillPaidPaymentAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\RejectPaymentRequest;
use App\Http\Requests\Admin\StoreCashPaymentRequest;
use App\Models\Member;
use App\Models\Package;
use App\Models\Payment;
use App\Notifications\MemberOperationalNotification;
use App\Notifications\Payments\PaymentRejectedNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminPaymentController extends Controller
{
    public function storeCash(StoreCashPaymentRequest $request, CreateCashPaymentAction $createCashPayment): RedirectResponse
    {
        try {
            $payment = $createCashPayment->handle(
                Member::query()->findOrFail($request->validated('member_id')),
                Package::query()->findOrFail($request->validated('package_id')),
                $request->user()->id,
                $request->integer('trainer_id') ?: null,
                $request->validated('note'),
            );
        } catch (\RuntimeException $exception) {
            return back()
                ->with('status', $exception->getMessage())
                ->with('status_kind', 'error')
                ->withInput();
        }

        return back()->with('status', 'Pembayaran tunai '.$payment->payment_code.' berhasil dicatat dan layanan member sudah aktif.');
    }

    public function approve(Request $request, Payment $payment, FulfillPaidPaymentAction $fulfillPaidPayment): RedirectResponse
    {
        abort_unless($request->user()?->can('verify_payments'), 403);

        $fulfillPaidPayment->handle($payment, $request->user()->id);

        return back()->with('status', 'Pembayaran berhasil disetujui dan layanan member diperbarui.');
    }

    public function reject(RejectPaymentRequest $request, Payment $payment): RedirectResponse
    {
        DB::transaction(function () use ($request, $payment): void {
            $payment = Payment::query()->with(['invoice', 'member.user', 'payable'])->lockForUpdate()->findOrFail($payment->id);

            if ($payment->status === 'paid') {
                return;
            }

            $payment->forceFill([
                'status' => 'rejected',
                'verified_by' => $request->user()->id,
                'verified_at' => now(),
                'rejected_reason' => $request->validated('reason'),
                'failure_reason' => $request->validated('reason'),
            ])->save();

            if ($payment->payable && $payment->payable->status !== 'active') {
                $payment->payable->forceFill(['status' => 'cancelled'])->save();
            }

            $payment->invoice?->forceFill(['status' => 'rejected'])->save();

            $payment->member?->user?->notify(new MemberOperationalNotification(
                'Pembayaran Ditolak',
                'Pembayaran '.$payment->payment_code.' ditolak. Periksa catatan transaksi untuk informasi lanjut.',
                route('member.transactions'),
                'Lihat Transaksi',
            ));
            $payment->member?->user?->notify((new PaymentRejectedNotification($payment))->afterCommit());
        });

        return back()->with('status', 'Pembayaran berhasil ditolak.');
    }
}
