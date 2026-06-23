<?php

namespace App\Http\Controllers\Admin;

use App\Features\Bookings\Actions\BookClassAction;
use App\Features\Bookings\Actions\CancelClassBookingAction;
use App\Features\Bookings\Actions\ConfirmClassBookingAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreAdminBookingRequest;
use App\Models\ClassEnrollment;
use App\Models\ClassSchedule;
use App\Models\Member;
use Carbon\CarbonImmutable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AdminBookingController extends Controller
{
    public function store(StoreAdminBookingRequest $request, BookClassAction $bookClass): RedirectResponse
    {
        try {
            $bookClass->handle(
                Member::query()->findOrFail($request->validated('member_id')),
                ClassSchedule::query()->findOrFail($request->validated('schedule_id')),
                CarbonImmutable::parse($request->validated('session_date')),
            );
        } catch (\RuntimeException $exception) {
            return back()
                ->with('status', $exception->getMessage())
                ->with('status_kind', 'error')
                ->withInput();
        }

        return back()->with('status', 'Booking kelas berhasil dibuat oleh admin.');
    }

    public function confirm(Request $request, ClassEnrollment $enrollment, ConfirmClassBookingAction $confirmBooking): RedirectResponse
    {
        abort_unless($request->user()?->can('manage_bookings'), 403);

        try {
            $confirmBooking->handle($enrollment);
        } catch (\RuntimeException $exception) {
            return back()
                ->with('status', $exception->getMessage())
                ->with('status_kind', 'error');
        }

        return back()->with('status', 'Booking kelas berhasil dikonfirmasi.');
    }

    public function cancel(Request $request, ClassEnrollment $enrollment, CancelClassBookingAction $cancelBooking): RedirectResponse
    {
        abort_unless($request->user()?->can('manage_bookings'), 403);

        try {
            $cancelBooking->handle($enrollment, 'Dibatalkan oleh admin.');
        } catch (\RuntimeException $exception) {
            return back()
                ->with('status', $exception->getMessage())
                ->with('status_kind', 'error');
        }

        return back()->with('status', 'Booking kelas berhasil dibatalkan.');
    }
}
