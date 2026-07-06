<?php

declare(strict_types=1);

namespace AhmedAshraf\Auth\Services\Sms;

use Illuminate\Support\Facades\Log;
use AhmedAshraf\Auth\Contracts\SmsSenderInterface;
use AhmedAshraf\Auth\Enums\OtpPurpose;
use AhmedAshraf\Auth\Models\User;

/**
 * Development fallback — logs OTP to laravel.log until a real SMS provider is bound.
 */
class LogSmsSender implements SmsSenderInterface
{
    public function sendOtp(string $phone, string $code, OtpPurpose $purpose, ?User $user = null): void
    {
        Log::channel(config('auth-package.sms.log_channel', 'stack'))->info('SMS OTP (no provider configured)', [
            'phone' => $phone,
            'code' => $code,
            'purpose' => $purpose->value,
            'user_id' => $user?->id,
        ]);
    }
}
