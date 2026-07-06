<?php

declare(strict_types=1);

namespace AhmedAshraf\Auth\Contracts;

use AhmedAshraf\Auth\Enums\OtpChannel;
use AhmedAshraf\Auth\Enums\OtpPurpose;
use AhmedAshraf\Auth\Models\Otp;

interface OtpRepositoryInterface
{
    public function create(array $data): Otp;

    public function findLatestActive(string $identifier, OtpChannel $channel, OtpPurpose $purpose): ?Otp;

    public function invalidateForIdentifier(string $identifier, OtpChannel $channel, OtpPurpose $purpose): void;

    public function markAsUsed(Otp $otp): void;

    public function incrementAttempts(Otp $otp): void;
}
