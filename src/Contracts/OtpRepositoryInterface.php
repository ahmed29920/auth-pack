<?php

declare(strict_types=1);

namespace Ashtech\LaravelAuthKit\Contracts;

use Ashtech\LaravelAuthKit\Enums\OtpChannel;
use Ashtech\LaravelAuthKit\Enums\OtpPurpose;
use Ashtech\LaravelAuthKit\Models\Otp;

interface OtpRepositoryInterface
{
    public function create(array $data): Otp;

    public function findLatestActive(string $identifier, OtpChannel $channel, OtpPurpose $purpose): ?Otp;

    public function invalidateForIdentifier(string $identifier, OtpChannel $channel, OtpPurpose $purpose): void;

    public function markAsUsed(Otp $otp): void;

    public function incrementAttempts(Otp $otp): void;
}
