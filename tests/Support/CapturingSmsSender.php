<?php

declare(strict_types=1);

namespace AhmedAshraf\Auth\Tests\Support;

use AhmedAshraf\Auth\Contracts\SmsSenderInterface;
use AhmedAshraf\Auth\Enums\OtpPurpose;
use AhmedAshraf\Auth\Models\User;

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
