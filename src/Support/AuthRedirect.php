<?php

declare(strict_types=1);

namespace AhmedAshraf\Auth\Support;

use AhmedAshraf\Auth\Models\User;

final class AuthRedirect
{
    public static function homeFor(User $user): string
    {
        $role = $user->getAuthRole();
        $roles = config('auth-package.redirects.roles', []);

        if ($role && isset($roles[$role])) {
            return $roles[$role];
        }

        return config('auth-package.redirects.default', '/');
    }
}
