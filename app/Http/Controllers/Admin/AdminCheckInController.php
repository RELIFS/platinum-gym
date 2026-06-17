<?php

namespace App\Http\Controllers\Admin;

use App\Features\CheckIns\Actions\ManualMemberCheckInAction;
use App\Features\CheckIns\Actions\ScanMemberQrAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ManualCheckInRequest;
use App\Http\Requests\Admin\ScanQrRequest;
use App\Models\Member;
use Illuminate\Http\RedirectResponse;
use RuntimeException;

class AdminCheckInController extends Controller
{
    public function scan(ScanQrRequest $request, ScanMemberQrAction $scanMemberQr): RedirectResponse
    {
        try {
            $checkIn = $scanMemberQr->handle($request->validated('token'), $request->user()->id);
        } catch (RuntimeException $exception) {
            return back()
                ->with('status', $exception->getMessage())
                ->with('status_kind', 'error')
                ->withInput();
        }

        return back()->with('status', 'Check-in berhasil untuk '.$checkIn->member?->user?->name.'.');
    }

    public function manual(ManualCheckInRequest $request, ManualMemberCheckInAction $manualCheckIn): RedirectResponse
    {
        try {
            $checkIn = $manualCheckIn->handle(Member::query()->findOrFail($request->validated('member_id')), $request->user()->id);
        } catch (RuntimeException $exception) {
            return back()
                ->with('status', $exception->getMessage())
                ->with('status_kind', 'error')
                ->withInput();
        }

        return back()->with('status', 'Check-in manual berhasil untuk '.$checkIn->member?->user?->name.'.');
    }
}
