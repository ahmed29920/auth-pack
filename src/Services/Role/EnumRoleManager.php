<?php

declare(strict_types=1);

namespace Ashtech\LaravelAuthKit\Services\Role;

use Ashtech\LaravelAuthKit\Contracts\RoleManagerInterface;
use Ashtech\LaravelAuthKit\Enums\UserRole;
use Ashtech\LaravelAuthKit\Models\User;

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
        if (! in_array($role, config('laravel-auth-kit.roles', []), true)) {
            throw new \InvalidArgumentException("Invalid role: {$role}");
        }
    }
}
