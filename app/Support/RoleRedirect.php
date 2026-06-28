<?php

namespace App\Support;

use App\Models\User;

/**
 * Resolve the post-login destination for an authenticated user based on the
 * highest-priority role attached to their account.
 *
 * Single-login, multi-portal pattern
 * ----------------------------------
 * Platinum Gym Padang intentionally exposes ONE login surface (`GET /login`)
 * shared by admin, owner, and member. After Auth::attempt() succeeds, the
 * controller calls RoleRedirect::pathFor() to send each user to the correct
 * portal:
 *
 *   - admin  -> /admin
 *   - owner  -> /owner
 *   - member -> /member/dashboard (or /member/complete-profile when the
 *               Member record has not been created yet, e.g. fresh Google OAuth
 *               sign-up that has not finished onboarding).
 *
 * Why one login URL instead of /admin/login + /login:
 *   - Smaller maintenance surface; rate limiting (LoginRequest, 5 attempts per
 *     email+IP), audit logging, and lockout policy live in one place.
 *   - A single `/login` URL does not advertise the existence of an admin area
 *     to the public, avoiding "security through obscurity" while keeping the
 *     real protections (throttling, role middleware, role-based redirects)
 *     centralized.
 *   - Users with overlapping roles (e.g. an owner who is also an admin) get a
 *     deterministic destination by priority order defined in ROLE_PATHS.
 *
 * Role priority is encoded by the array order below (admin -> owner -> member).
 * If you add a new role, decide its priority deliberately because the first
 * matching role wins.
 */
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
