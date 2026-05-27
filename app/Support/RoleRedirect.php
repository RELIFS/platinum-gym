<?php

namespace App\Support;

use App\Models\User;

class RoleRedirect
{
    /**
     * @var array<string, string>
     */
    private const ROLE_PATHS = [
        'admin' => '/admin',
        'owner' => '/owner',
        'member' => '/member/dashboard',
    ];

    public static function pathFor(User $user): ?string
    {
        foreach (self::ROLE_PATHS as $role => $path) {
            if ($user->hasRole($role)) {
                if ($role === 'member' && ! $user->member()->exists()) {
                    return '/member/complete-profile';
                }

                return $path;
            }
        }

        return null;
    }
}
