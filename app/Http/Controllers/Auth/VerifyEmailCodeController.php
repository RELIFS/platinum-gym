<?php

namespace App\Http\Controllers\Auth;

use App\Features\Auth\Actions\VerifyEmailVerificationCodeAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\VerifyEmailCodeRequest;
use App\Support\RoleRedirect;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class VerifyEmailCodeController extends Controller
{
    public function __invoke(VerifyEmailCodeRequest $request, VerifyEmailVerificationCodeAction $verify): RedirectResponse
    {
        $verify->handle($request->user(), $request->validated('code'));

        $path = RoleRedirect::pathFor($request->user());

        if ($path === null) {
            Auth::guard('web')->logout();

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->withErrors([
                'email' => __('Akun belum memiliki role valid. Hubungi admin.'),
            ]);
        }

        return redirect()->intended($path.'?verified=1');
    }
}
