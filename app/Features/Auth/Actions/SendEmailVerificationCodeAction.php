<?php

namespace App\Features\Auth\Actions;

use App\Features\Auth\Support\MaskedEmail;
use App\Models\EmailVerificationCode;
use App\Models\User;
use App\Notifications\Auth\EmailVerificationCodeNotification;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\URL;

class SendEmailVerificationCodeAction
{
    public function handle(User $user): void
    {
        if ($user->hasVerifiedEmail()) {
            return;
        }

        $code = (string) random_int(100000, 999999);

        EmailVerificationCode::query()
            ->whereBelongsTo($user)
            ->whereNull('verified_at')
            ->delete();

        $expiresAt = now()->addMinutes(10);

        EmailVerificationCode::create([
            'user_id' => $user->id,
            'code_hash' => Hash::make($code),
            'attempts' => 0,
            'expires_at' => $expiresAt,
            'sent_at' => now(),
        ]);

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->email)]
        );

        $user->notify((new EmailVerificationCodeNotification(
            code: $code,
            maskedEmail: MaskedEmail::forDisplay($user->email),
            verificationUrl: $verificationUrl,
            expiresAt: $expiresAt,
        ))->afterCommit());
    }
}
