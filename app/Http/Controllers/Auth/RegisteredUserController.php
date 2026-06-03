<?php

namespace App\Http\Controllers\Auth;

use App\Features\Auth\Actions\RegisterMemberAction;
use App\Features\Auth\Http\Requests\RegisterMemberRequest;
use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     */
    public function store(RegisterMemberRequest $request, RegisterMemberAction $registerMember): RedirectResponse
    {
        $user = $registerMember->execute($request->validated());

        event(new Registered($user));

        Auth::login($user);

        return redirect(route('verification.notice', absolute: false));
    }
}
