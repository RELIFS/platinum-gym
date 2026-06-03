<?php

namespace App\Features\Auth\Actions;

use App\Models\SocialAccount;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Laravel\Socialite\Contracts\User as SocialiteUser;
use Spatie\Permission\Models\Role;

class ResolveGoogleUserAction
{
    public function execute(SocialiteUser $googleUser): User
    {
        return DB::transaction(fn () => $this->resolve($googleUser));
    }

    private function resolve(SocialiteUser $googleUser): User
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
}
