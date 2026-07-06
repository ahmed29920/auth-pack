<?php

declare(strict_types=1);

namespace AhmedAshraf\Auth\Repositories;

use Illuminate\Database\Eloquent\Model;
use AhmedAshraf\Auth\Contracts\UserRepositoryInterface;
use AhmedAshraf\Auth\Models\User;

class UserRepository implements UserRepositoryInterface
{
    public function __construct(
        protected User $model,
    ) {}

    public function findById(int $id): ?User
    {
        return $this->model->newQuery()->find($id);
    }

    public function findByEmail(string $email): ?User
    {
        return $this->model->newQuery()->where('email', $email)->first();
    }

    public function findByPhone(string $phone): ?User
    {
        return $this->model->newQuery()->where('phone', $phone)->first();
    }

    public function findByEmailOrPhone(string $identifier): ?User
    {
        return $this->model->newQuery()
            ->where(function ($query) use ($identifier) {
                $query->where('email', $identifier)
                    ->orWhere('phone', $identifier);
            })
            ->first();
    }

    public function create(array $data): User
    {
        return $this->model->newQuery()->create($data);
    }

    public function update(Model $user, array $data): User
    {
        $user->update($data);

        return $user->fresh();
    }

    public function markEmailVerified(User $user): User
    {
        return $this->update($user, ['email_verified_at' => now()]);
    }

    public function markPhoneVerified(User $user): User
    {
        return $this->update($user, ['phone_verified_at' => now()]);
    }
}
