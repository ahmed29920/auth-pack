<?php

declare(strict_types=1);

namespace AhmedAshraf\Auth\Traits;

use AhmedAshraf\Auth\Contracts\RoleManagerInterface;
use AhmedAshraf\Auth\Enums\UserRole;

trait ManagesAuthRoles
{
    public function assignRole(UserRole|string $role): void
    {
        $roleValue = $role instanceof UserRole ? $role->value : $role;

        app(RoleManagerInterface::class)->assign($this, $roleValue);
    }

    public function syncRole(UserRole|string $role): void
    {
        $roleValue = $role instanceof UserRole ? $role->value : $role;

        app(RoleManagerInterface::class)->sync($this, $roleValue);
    }

    public function hasRole(UserRole|string $role): bool
    {
        $roleValue = $role instanceof UserRole ? $role->value : $role;

        return app(RoleManagerInterface::class)->hasRole($this, $roleValue);
    }

    public function getAuthRole(): ?string
    {
        return app(RoleManagerInterface::class)->getRole($this);
    }

    public function getAuthRoles(): array
    {
        return app(RoleManagerInterface::class)->getRoles($this);
    }
}
