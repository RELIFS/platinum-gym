<?php

namespace App\Http\Controllers\Auth;

use App\Features\Auth\Actions\CompleteMemberProfileAction;
use App\Features\Auth\Http\Requests\CompleteMemberProfileRequest;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CompleteMemberProfileController extends Controller
{
    public function create(Request $request): View|RedirectResponse
    {
        if ($request->user()->member()->exists()) {
            return redirect()->route('member.dashboard');
        }

        return view('auth.complete-profile');
    }

    public function store(CompleteMemberProfileRequest $request, CompleteMemberProfileAction $completeMemberProfile): RedirectResponse
    {
        $user = $request->user();

        if ($user->member()->exists()) {
            return redirect()->route('member.dashboard');
        }

        $completeMemberProfile->execute($user, $request->validated());

        return redirect()->route('member.dashboard')->with('status', __('Profil member berhasil dilengkapi.'));
    }
}
