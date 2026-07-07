<?php

declare(strict_types=1);

namespace Ashtech\LaravelAuthKit\Support;

use Ashtech\LaravelAuthKit\Models\User;

final class AuthRedirect
{
    public static function homeFor(User $user): string
    {
        $role = $user->getAuthRole();
        $roles = config('laravel-auth-kit.redirects.roles', []);

        if ($role && isset($roles[$role])) {
            return $roles[$role];
        }

        return config('laravel-auth-kit.redirects.default', '/');
    }
}
