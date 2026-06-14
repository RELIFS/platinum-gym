<?php

namespace App\Http\Controllers\Admin;

use App\Features\Bookings\Actions\BookClassAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreAdminBookingRequest;
use App\Models\ClassEnrollment;
use App\Models\ClassSchedule;
use App\Models\Member;
use App\Notifications\MemberOperationalNotification;
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
            return back()->with('status', $exception->getMessage())->withInput();
        }

        return back()->with('status', 'Booking kelas berhasil dibuat oleh admin.');
    }

    public function confirm(Request $request, ClassEnrollment $enrollment): RedirectResponse
    {
        abort_unless($request->user()?->can('manage_bookings'), 403);

        $enrollment->forceFill(['status' => 'confirmed'])->save();
        $enrollment->loadMissing(['member.user', 'schedule.gymClass']);

        $enrollment->member?->user?->notify(new MemberOperationalNotification(
            'Booking Kelas Dikonfirmasi',
            'Booking '.$enrollment->schedule?->gymClass?->name.' sudah dikonfirmasi oleh admin.',
            route('member.bookings'),
            'Lihat Booking',
        ));

        return back()->with('status', 'Booking kelas berhasil dikonfirmasi.');
    }

    public function cancel(Request $request, ClassEnrollment $enrollment): RedirectResponse
    {
        abort_unless($request->user()?->can('manage_bookings'), 403);

        $enrollment->forceFill([
            'status' => 'cancelled',
            'cancelled_at' => now(),
            'cancel_reason' => 'Dibatalkan oleh admin.',
        ])->save();
        $enrollment->loadMissing(['member.user', 'schedule.gymClass']);

        $enrollment->member?->user?->notify(new MemberOperationalNotification(
            'Booking Kelas Dibatalkan',
            'Booking '.$enrollment->schedule?->gymClass?->name.' dibatalkan oleh admin.',
            route('member.bookings'),
            'Lihat Booking',
        ));

        return back()->with('status', 'Booking kelas berhasil dibatalkan.');
    }
}
