<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\SocialAccount;
use App\Models\User;
use App\Support\RoleRedirect;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Laravel\Socialite\Contracts\User as SocialiteUser;
use Laravel\Socialite\Facades\Socialite;
use Spatie\Permission\Models\Role;
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

    public function callback(Request $request): RedirectResponse
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

        $user = DB::transaction(fn () => $this->findOrCreateUser($googleUser));

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

    private function findOrCreateUser(SocialiteUser $googleUser): User
    {
        $socialAccount = SocialAccount::query()
            ->where('provider', 'google')
            ->where('provider_user_id', $googleUser->getId())
            ->first();

        if ($socialAccount !== null) {
            $this->updateSocialAccount($socialAccount, $googleUser);

            return $socialAccount->user;
        }

        $user = User::query()->where('email', $googleUser->getEmail())->first();

        if ($user === null) {
            $user = User::create([
                'name' => $googleUser->getName() ?: $googleUser->getNickname() ?: (string) str($googleUser->getEmail())->before('@')->headline(),
                'email' => $googleUser->getEmail(),
                'avatar' => $googleUser->getAvatar(),
                'password' => null,
                'status' => 'active',
            ]);

            $user->forceFill(['email_verified_at' => now()])->save();
            $user->assignRole(Role::findOrCreate('member', 'web'));
        } elseif (! $user->hasVerifiedEmail()) {
            $user->forceFill(['email_verified_at' => now()])->save();
        }

        $socialAccount = $user->socialAccounts()->create([
            'provider' => 'google',
            'provider_user_id' => $googleUser->getId(),
        ]);

        $this->updateSocialAccount($socialAccount, $googleUser);

        return $user;
    }

    private function updateSocialAccount(SocialAccount $socialAccount, SocialiteUser $googleUser): void
    {
        $attributes = [
            'provider_email' => $googleUser->getEmail(),
            'provider_avatar' => $googleUser->getAvatar(),
            'access_token' => $googleUser->token ?? null,
            'token_expires_at' => isset($googleUser->expiresIn) ? now()->addSeconds((int) $googleUser->expiresIn) : null,
        ];

        if (filled($googleUser->refreshToken ?? null)) {
            $attributes['refresh_token'] = $googleUser->refreshToken;
        }

        $socialAccount->forceFill($attributes)->save();
    }

    private function hasGoogleConfig(): bool
    {
        return filled(config('services.google.client_id'))
            && filled(config('services.google.client_secret'))
            && filled(config('services.google.redirect'));
    }
}
