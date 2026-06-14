<?php

namespace App\Http\Controllers\Member;

use App\Features\Bookings\Actions\BookClassAction;
use App\Features\Bookings\Actions\CancelClassBookingAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Member\BookClassRequest;
use App\Models\ClassEnrollment;
use App\Models\ClassSchedule;
use Carbon\CarbonImmutable;
use Illuminate\Http\RedirectResponse;
use RuntimeException;

class MemberBookingController extends Controller
{
    public function store(BookClassRequest $request, ClassSchedule $schedule, BookClassAction $bookClass): RedirectResponse
    {
        try {
            $result = $bookClass->handle(
                $request->user()->member()->firstOrFail(),
                $schedule,
                CarbonImmutable::parse($request->validated('session_date')),
            );
        } catch (RuntimeException $exception) {
            return back()->with('status', $exception->getMessage())->withInput();
        }

        if ($result['payment']) {
            return redirect()->route('member.transactions.show', $result['payment'])
                ->with('status', 'Booking kelas membutuhkan pembayaran. Lanjutkan pembayaran untuk mengaktifkan booking.');
        }

        return redirect()->route('member.bookings')->with('status', 'Booking kelas berhasil tercatat.');
    }

    public function destroy(ClassEnrollment $enrollment, CancelClassBookingAction $cancelClassBooking): RedirectResponse
    {
        $this->authorize('cancel', $enrollment);

        try {
            $cancelClassBooking->handle($enrollment);
        } catch (RuntimeException $exception) {
            return back()->with('status', $exception->getMessage());
        }

        return redirect()->route('member.bookings')->with('status', 'Booking kelas berhasil dibatalkan.');
    }
}
