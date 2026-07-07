<?php

declare(strict_types=1);

namespace Ashtech\LaravelAuthKit\Contracts;

use Ashtech\LaravelAuthKit\Enums\OtpPurpose;
use Ashtech\LaravelAuthKit\Models\User;

interface SmsSenderInterface
{
    /**
     * Send an OTP code via SMS.
     *
     * Implement this in the host application and bind it in the service container
     * or set AUTH_KIT_SMS_SENDER in .env to your class name.
     */
    public function sendOtp(string $phone, string $code, OtpPurpose $purpose, ?User $user = null): void;
}
