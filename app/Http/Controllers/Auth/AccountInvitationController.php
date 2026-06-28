<?php

namespace App\Http\Controllers\Auth;

use App\Features\Auth\Actions\AcceptAccountInvitationAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\AcceptAccountInvitationRequest;
use App\Models\AccountInvitation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AccountInvitationController extends Controller
{
    public function show(string $token): View
    {
        $invitation = AccountInvitation::query()
            ->with('user')
            ->where('token_hash', hash('sha256', $token))
            ->first();

        abort_unless($invitation?->isAcceptable(), 404);

        return view('auth.accept-invitation', [
            'token' => $token,
            'invitation' => $invitation,
        ]);
    }

    public function store(AcceptAccountInvitationRequest $request, string $token, AcceptAccountInvitationAction $accept): RedirectResponse
    {
        $result = $accept->handle($token, $request->validated('password'));

        Auth::login($result['user']);
        $request->session()->regenerate();

        return redirect($result['redirect'])->with('status', 'Akun berhasil diaktifkan. Selamat datang di Platinum Gym Padang.');
    }
}
