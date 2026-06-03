<?php

namespace App\Policies;

use App\Models\ClassEnrollment;
use App\Models\User;

class ClassEnrollmentPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        return $user->hasRole('admin') ? true : null;
    }

    public function viewAny(User $user): bool
    {
        return false;
    }

    public function view(User $user, ClassEnrollment $classEnrollment): bool
    {
        return $classEnrollment->member?->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->hasRole('member') && $user->can('book_class');
    }

    public function update(User $user, ClassEnrollment $classEnrollment): bool
    {
        return $classEnrollment->member?->user_id === $user->id && $user->can('cancel_own_booking');
    }

    public function cancel(User $user, ClassEnrollment $classEnrollment): bool
    {
        return $this->update($user, $classEnrollment);
    }

    public function delete(User $user, ClassEnrollment $classEnrollment): bool
    {
        return false;
    }
}
