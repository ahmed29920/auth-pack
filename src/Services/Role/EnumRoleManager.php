<?php

declare(strict_types=1);

namespace AhmedAshraf\Auth\Services\Role;

use AhmedAshraf\Auth\Contracts\RoleManagerInterface;
use AhmedAshraf\Auth\Enums\UserRole;
use AhmedAshraf\Auth\Models\User;

class EnumRoleManager implements RoleManagerInterface
{
    public function assign(User $user, string $role): void
    {
        $this->assertValidRole($role);

        $user->forceFill(['role' => $role])->save();
    }

    public function sync(User $user, string $role): void
    {
        $this->assign($user, $role);
    }

    public function hasRole(User $user, string $role): bool
    {
        $current = $user->role instanceof UserRole ? $user->role->value : (string) $user->role;

        return $current === $role;
    }

    public function getRole(User $user): ?string
    {
        return $user->role instanceof UserRole ? $user->role->value : (string) $user->role;
    }

    public function getRoles(User $user): array
    {
        $role = $this->getRole($user);

        return $role ? [$role] : [];
    }

    protected function assertValidRole(string $role): void
    {
        if (! in_array($role, config('auth-package.roles', []), true)) {
            throw new \InvalidArgumentException("Invalid role: {$role}");
        }
    }
}
