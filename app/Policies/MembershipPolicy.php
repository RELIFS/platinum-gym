<?php

namespace App\Policies;

use App\Models\Membership;
use App\Models\User;

class MembershipPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        return $user->hasRole('admin') ? true : null;
    }

    public function viewAny(User $user): bool
    {
        return false;
    }

    public function view(User $user, Membership $membership): bool
    {
        return $membership->member?->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->hasRole('member') && $user->can('buy_membership');
    }

    public function update(User $user, Membership $membership): bool
    {
        return false;
    }

    public function delete(User $user, Membership $membership): bool
    {
        return false;
    }
}
