<?php

namespace App\Policies;

use App\Models\Member;
use App\Models\QrToken;
use App\Models\User;

class QrTokenPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        return $user->hasRole('admin') ? true : null;
    }

    public function viewAny(User $user): bool
    {
        return false;
    }

    public function view(User $user, QrToken $qrToken): bool
    {
        return $this->ownsToken($user, $qrToken) && $user->can('view_own_qr');
    }

    public function update(User $user, QrToken $qrToken): bool
    {
        return false;
    }

    public function delete(User $user, QrToken $qrToken): bool
    {
        return false;
    }

    private function ownsToken(User $user, QrToken $qrToken): bool
    {
        return $qrToken->tokenable instanceof Member
            && $qrToken->tokenable->user_id === $user->id;
    }
}
