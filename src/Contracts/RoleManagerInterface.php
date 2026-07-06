<?php

declare(strict_types=1);

namespace AhmedAshraf\Auth\Contracts;

use AhmedAshraf\Auth\Models\User;

interface RoleManagerInterface
{
    public function assign(User $user, string $role): void;

    public function sync(User $user, string $role): void;

    public function hasRole(User $user, string $role): bool;

    public function getRole(User $user): ?string;

    public function getRoles(User $user): array;
}
