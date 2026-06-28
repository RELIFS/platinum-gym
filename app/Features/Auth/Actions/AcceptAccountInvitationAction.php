<?php

namespace App\Features\Auth\Actions;

use App\Models\AccountInvitation;
use App\Models\User;
use App\Support\RoleRedirect;
use Illuminate\Auth\Events\Verified;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AcceptAccountInvitationAction
{
    /**
     * @return array{user: User, redirect: string}
     */
    public function handle(string $token, string $password): array
    {
        return DB::transaction(function () use ($token, $password): array {
            $invitation = AccountInvitation::query()
                ->with('user.member')
                ->where('token_hash', hash('sha256', $token))
                ->lockForUpdate()
                ->first();

            if (! $invitation || ! $invitation->isAcceptable()) {
                throw ValidationException::withMessages([
                    'token' => 'Undangan akun sudah tidak berlaku. Minta admin mengirim ulang undangan.',
                ]);
            }

            $user = $invitation->user;
            $user->forceFill([
                'password' => Hash::make($password),
                'email_verified_at' => $user->email_verified_at ?? now(),
            ])->save();

            if ($user->wasChanged('email_verified_at')) {
                event(new Verified($user));
            }

            $invitation->forceFill(['accepted_at' => now()])->save();

            return [
                'user' => $user->refresh(),
                'redirect' => RoleRedirect::pathFor($user) ?? route('dashboard', absolute: false),
            ];
        });
    }
}
