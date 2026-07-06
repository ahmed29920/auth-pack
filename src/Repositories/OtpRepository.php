<?php

declare(strict_types=1);

namespace AhmedAshraf\Auth\Repositories;

use AhmedAshraf\Auth\Contracts\OtpRepositoryInterface;
use AhmedAshraf\Auth\Enums\OtpChannel;
use AhmedAshraf\Auth\Enums\OtpPurpose;
use AhmedAshraf\Auth\Models\Otp;

class OtpRepository implements OtpRepositoryInterface
{
    public function __construct(
        protected Otp $model,
    ) {}

    public function create(array $data): Otp
    {
        return $this->model->newQuery()->create($data);
    }

    public function findLatestActive(string $identifier, OtpChannel $channel, OtpPurpose $purpose): ?Otp
    {
        return $this->model->newQuery()
            ->where('identifier', $identifier)
            ->where('channel', $channel)
            ->where('purpose', $purpose)
            ->where('is_used', false)
            ->where('expires_at', '>', now())
            ->latest()
            ->first();
    }

    public function invalidateForIdentifier(string $identifier, OtpChannel $channel, OtpPurpose $purpose): void
    {
        $this->model->newQuery()
            ->where('identifier', $identifier)
            ->where('channel', $channel)
            ->where('purpose', $purpose)
            ->where('is_used', false)
            ->update(['is_used' => true]);
    }

    public function markAsUsed(Otp $otp): void
    {
        $otp->update(['is_used' => true]);
    }

    public function incrementAttempts(Otp $otp): void
    {
        $otp->increment('attempts');
    }
}
