<?php

declare(strict_types=1);

namespace Ashtech\LaravelAuthKit\Tests\Support;

use Ashtech\LaravelAuthKit\Contracts\SmsSenderInterface;
use Ashtech\LaravelAuthKit\Enums\OtpPurpose;
use Ashtech\LaravelAuthKit\Models\User;

final class CapturingSmsSender implements SmsSenderInterface
{
    public static ?string $lastCode = null;

    public static ?string $lastPhone = null;

    public function sendOtp(string $phone, string $code, OtpPurpose $purpose, ?User $user = null): void
    {
        self::$lastPhone = $phone;
        self::$lastCode = $code;
    }

    public static function reset(): void
    {
        self::$lastCode = null;
        self::$lastPhone = null;
    }
}
