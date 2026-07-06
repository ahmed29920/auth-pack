<?php

declare(strict_types=1);

namespace AhmedAshraf\Auth\Contracts;

use Illuminate\Database\Eloquent\Model;
use AhmedAshraf\Auth\Models\User;

interface UserRepositoryInterface
{
    public function findById(int $id): ?User;

    public function findByEmail(string $email): ?User;

    public function findByPhone(string $phone): ?User;

    public function findByEmailOrPhone(string $identifier): ?User;

    public function create(array $data): User;

    public function update(Model $user, array $data): User;

    public function markEmailVerified(User $user): User;

    public function markPhoneVerified(User $user): User;
}
