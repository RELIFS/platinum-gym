<?php

namespace App\Http\Controllers\Member;

use App\Features\MemberPortal\Queries\MemberDashboardQuery;
use App\Features\Payments\Actions\CreatePaymentCheckoutAction;
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
            return back()->with('status', $exception->getMessage())->withInput();
        }

        return redirect()->route('member.transactions.show', $payment)->with('status', 'Checkout membership berhasil dibuat. Lanjutkan pembayaran melalui Midtrans Sandbox.');
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
            return back()->with('status', $exception->getMessage())->withInput();
        }

        return redirect()->route('member.transactions.show', $payment)->with('status', 'Checkout paket sesi berhasil dibuat. Lanjutkan pembayaran melalui Midtrans Sandbox.');
    }

    public function show(Request $request, Payment $payment, MemberDashboardQuery $query): View
    {
        $this->authorize('view', $payment);

        return view('member.payment-show', [
            'portal' => $query->forUser($request->user()),
            'payment' => $payment->load(['invoice', 'payable', 'member.user']),
        ]);
    }

    public function pay(Request $request, Payment $payment): RedirectResponse
    {
        $this->authorize('view', $payment);

        if (! in_array($payment->status, ['waiting_payment', 'pending', 'unpaid'], true)) {
            return back()->with('status', 'Pembayaran ini tidak membutuhkan tindakan bayar.');
        }

        if (blank($payment->midtrans_redirect_url)) {
            return back()->with('status', 'Link pembayaran Midtrans belum tersedia.');
        }

        return redirect()->away($payment->midtrans_redirect_url);
    }
}
