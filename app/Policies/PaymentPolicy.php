<?php

namespace App\Policies;

use App\Models\Payment;
use App\Models\User;

class PaymentPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        return $user->hasRole('admin') ? true : null;
    }

    public function viewAny(User $user): bool
    {
        return false;
    }

    public function view(User $user, Payment $payment): bool
    {
        return $payment->member?->user_id === $user->id;
    }

    public function update(User $user, Payment $payment): bool
    {
        return $payment->member?->user_id === $user->id && $user->can('upload_own_payment_proof');
    }

    public function delete(User $user, Payment $payment): bool
    {
        return false;
    }
}
