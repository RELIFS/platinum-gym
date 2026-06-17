<?php

namespace App\Http\Controllers\Member;

use App\Features\MemberPortal\Queries\MemberDashboardQuery;
use App\Features\MemberPortal\ViewModels\MemberPortalStatusViewModel;
use App\Features\Payments\Actions\CreatePaymentCheckoutAction;
use App\Features\Payments\Actions\SyncMidtransPaymentStatusAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Member\CheckoutPackageRequest;
use App\Models\Package;
use App\Models\Payment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use RuntimeException;

class MemberCheckoutController extends Controller
{
    public function membership(CheckoutPackageRequest $request, Package $package, CreatePaymentCheckoutAction $checkout): RedirectResponse
    {
        try {
            $payment = $checkout->membership($request->user()->member()->firstOrFail(), $package);
        } catch (RuntimeException $exception) {
            return back()
                ->with('status', $exception->getMessage())
                ->with('status_kind', 'error')
                ->withInput();
        }

        return redirect()->route('member.transactions.show', $payment)->with('status', 'Checkout membership berhasil dibuat. Lanjutkan pembayaran melalui Midtrans.');
    }

    public function packageSession(CheckoutPackageRequest $request, Package $package, CreatePaymentCheckoutAction $checkout): RedirectResponse
    {
        try {
            $payment = $checkout->packageSession(
                $request->user()->member()->firstOrFail(),
                $package,
                $request->integer('trainer_id') ?: null,
            );
        } catch (RuntimeException $exception) {
            return back()
                ->with('status', $exception->getMessage())
                ->with('status_kind', 'error')
                ->withInput();
        }

        return redirect()->route('member.transactions.show', $payment)->with('status', 'Checkout paket sesi berhasil dibuat. Lanjutkan pembayaran melalui Midtrans.');
    }

    public function show(Request $request, Payment $payment, MemberDashboardQuery $query, SyncMidtransPaymentStatusAction $sync): View
    {
        $this->authorize('view', $payment);

        // Pull the latest Midtrans status before rendering. Acts as a safety net
        // when webhooks are unreachable (e.g. local dev) or delayed in production.
        if ($this->shouldSyncBeforeShow($payment)) {
            $payment = $sync->handle($payment);
        }

        $payment->load(['invoice', 'payable', 'member.user']);

        return view('member.payment-show', [
            'portal' => $query->forUser($request->user()),
            'payment' => $payment,
            'paymentStatus' => MemberPortalStatusViewModel::payment($payment),
            'invoiceStatusLabel' => MemberPortalStatusViewModel::invoiceLabel($payment->invoice?->status),
        ]);
    }

    public function pay(Request $request, Payment $payment, SyncMidtransPaymentStatusAction $sync): RedirectResponse
    {
        $this->authorize('view', $payment);

        // Re-check status before pushing the user back to Midtrans Snap, in case
        // the previous attempt has already settled.
        if ($this->shouldSyncBeforeShow($payment)) {
            $payment = $sync->handle($payment);
        }

        if ($payment->status === 'paid') {
            return redirect()
                ->route('member.transactions.show', $payment)
                ->with('status', 'Pembayaran sudah lunas. Layanan Anda telah diperbarui.');
        }

        if (! in_array($payment->status, ['waiting_payment', 'pending', 'unpaid'], true)) {
            return back()->with('status', 'Pembayaran ini tidak membutuhkan tindakan bayar.');
        }

        if (blank($payment->midtrans_redirect_url)) {
            return back()->with('status', 'Link pembayaran Midtrans belum tersedia.');
        }

        return redirect()->away($payment->midtrans_redirect_url);
    }

    private function shouldSyncBeforeShow(Payment $payment): bool
    {
        if ($payment->method !== 'midtrans') {
            return false;
        }

        if (blank($payment->midtrans_order_id)) {
            return false;
        }

        return in_array($payment->status, ['waiting_payment', 'pending', 'unpaid'], true);
    }
}
