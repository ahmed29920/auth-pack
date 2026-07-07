<?php

declare(strict_types=1);

namespace Ashtech\LaravelAuthKit\Services\Role;

use Ashtech\LaravelAuthKit\Contracts\RoleManagerInterface;
use Ashtech\LaravelAuthKit\Models\User;
use Ashtech\LaravelAuthKit\Services\Role\EnumRoleManager;

class SpatieRoleManager implements RoleManagerInterface
{
    public function __construct(
        protected EnumRoleManager $fallback,
    ) {}

    public function assign(User $user, string $role): void
    {
        if ($this->spatieAvailable()) {
            $user->assignRole($role);

            return;
        }

        $this->fallback->assign($user, $role);
    }

    public function sync(User $user, string $role): void
    {
        if ($this->spatieAvailable()) {
            $user->syncRoles([$role]);

            return;
        }

        $this->fallback->sync($user, $role);
    }

    public function hasRole(User $user, string $role): bool
    {
        if ($this->spatieAvailable()) {
            return $user->hasRole($role);
        }

        return $this->fallback->hasRole($user, $role);
    }

    public function getRole(User $user): ?string
    {
        if ($this->spatieAvailable()) {
            return $user->getRoleNames()->first();
        }

        return $this->fallback->getRole($user);
    }

    public function getRoles(User $user): array
    {
        if ($this->spatieAvailable()) {
            return $user->getRoleNames()->toArray();
        }

        return $this->fallback->getRoles($user);
    }

    protected function spatieAvailable(): bool
    {
        return trait_exists(\Spatie\Permission\Traits\HasRoles::class)
            && in_array(\Spatie\Permission\Traits\HasRoles::class, class_uses_recursive(config('laravel-auth-kit.user_model')), true);
    }
}
