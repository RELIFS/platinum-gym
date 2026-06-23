<?php

namespace App\Http\Controllers;

use App\Features\Admin\Queries\AdminDashboardQuery;
use App\Features\MemberPortal\Queries\MemberDashboardQuery;
use App\Features\Reports\Queries\OwnerReportQuery;
use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        $user = $request->user();

        if ($user->hasRole('admin')) {
            $query = app(AdminDashboardQuery::class);

            return view('admin.pages.account-security', [
                'user' => $user,
                'portal' => $query->forUser($user),
                'navigation' => $query->navigation(),
            ]);
        }

        if ($user->hasRole('owner')) {
            $query = app(OwnerReportQuery::class);

            return view('owner.account-security', [
                'user' => $user,
                'portal' => ['owner' => $user],
                'navigation' => $query->navigation(),
            ]);
        }

        if ($user->hasRole('member') && $user->member) {
            $query = app(MemberDashboardQuery::class);

            return view('member.pages.account-security', [
                'user' => $user,
                'portal' => $query->forUser($user),
            ]);
        }

        return view('profile.edit', [
            'user' => $user,
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
