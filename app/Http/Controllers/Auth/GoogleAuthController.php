<?php

namespace App\Http\Controllers\Auth;

use App\Features\Auth\Actions\ResolveGoogleUserAction;
use App\Http\Controllers\Controller;
use App\Support\RoleRedirect;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Throwable;

class GoogleAuthController extends Controller
{
    public function redirect(): RedirectResponse
    {
        if (! $this->hasGoogleConfig()) {
            return redirect()->route('login')->withErrors([
                'email' => __('Login Google belum dikonfigurasi.'),
            ]);
        }

        return Socialite::driver('google')->redirect();
    }

    public function callback(Request $request, ResolveGoogleUserAction $resolveGoogleUser): RedirectResponse
    {
        if (! $this->hasGoogleConfig()) {
            return redirect()->route('login')->withErrors([
                'email' => __('Login Google belum dikonfigurasi.'),
            ]);
        }

        try {
            $googleUser = Socialite::driver('google')->stateless()->user();
        } catch (Throwable) {
            return redirect()->route('login')->withErrors([
                'email' => __('Login Google dibatalkan atau gagal. Silakan coba lagi.'),
            ]);
        }

        if (blank($googleUser->getEmail())) {
            return redirect()->route('login')->withErrors([
                'email' => __('Akun Google tidak mengirim alamat email.'),
            ]);
        }

        if (blank($googleUser->getId())) {
            return redirect()->route('login')->withErrors([
                'email' => __('Akun Google tidak mengirim ID pengguna.'),
            ]);
        }

        $user = $resolveGoogleUser->execute($googleUser);

        Auth::login($user);

        $request->session()->regenerate();

        $user->forceFill(['last_login_at' => now()])->save();

        $path = RoleRedirect::pathFor($user);

        if ($path === null) {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->withErrors([
                'email' => __('Akun belum memiliki role valid. Hubungi admin.'),
            ]);
        }

        return redirect()->intended($path);
    }

    private function hasGoogleConfig(): bool
    {
        return filled(config('services.google.client_id'))
            && filled(config('services.google.client_secret'))
            && filled(config('services.google.redirect'));
    }
}
