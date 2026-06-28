<?php

namespace App\Features\Auth\Actions;

use App\Models\EmailVerificationCode;
use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class VerifyEmailVerificationCodeAction
{
    private const MAX_ATTEMPTS = 5;

    public function handle(User $user, string $code): void
    {
        if ($user->hasVerifiedEmail()) {
            return;
        }

        $verification = EmailVerificationCode::query()
            ->whereBelongsTo($user)
            ->whereNull('verified_at')
            ->latest('created_at')
            ->first();

        if (! $verification) {
            throw ValidationException::withMessages([
                'code' => 'Kode verifikasi belum tersedia. Kirim ulang kode terlebih dahulu.',
            ]);
        }

        if ($verification->expires_at->isPast()) {
            throw ValidationException::withMessages([
                'code' => 'Kode verifikasi sudah kedaluwarsa. Kirim ulang kode baru.',
            ]);
        }

        if ($verification->attempts >= self::MAX_ATTEMPTS) {
            throw ValidationException::withMessages([
                'code' => 'Terlalu banyak percobaan. Kirim ulang kode baru.',
            ]);
        }

        if (! Hash::check($code, $verification->code_hash)) {
            $verification->increment('attempts');

            throw ValidationException::withMessages([
                'code' => 'Kode verifikasi belum sesuai.',
            ]);
        }

        if ($user->markEmailAsVerified()) {
            event(new Verified($user));
        }

        $verification->forceFill(['verified_at' => now()])->save();

        EmailVerificationCode::query()
            ->whereBelongsTo($user)
            ->whereKeyNot($verification->id)
            ->whereNull('verified_at')
            ->delete();
    }
}
