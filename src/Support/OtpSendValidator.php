<?php

declare(strict_types=1);

namespace Ashtech\LaravelAuthKit\Support;

use Ashtech\LaravelAuthKit\Enums\OtpChannel;
use Ashtech\LaravelAuthKit\Enums\OtpPurpose;
use Ashtech\LaravelAuthKit\Models\User;

final class OtpSendValidator
{
    /**
     * @return list<OtpPurpose>
     */
    public static function guestSendPurposes(): array
    {
        return [
            OtpPurpose::Login,
            OtpPurpose::Register,
            OtpPurpose::ResetPassword,
        ];
    }

    public static function channelEnabled(OtpChannel $channel): bool
    {
        return match ($channel) {
            OtpChannel::Phone => AuthMethods::phoneForOtp(),
            OtpChannel::Email => AuthMethods::emailForOtp(),
        };
    }

    public static function shouldSend(OtpPurpose $purpose, OtpChannel $channel, ?User $user): bool
    {
        if (! self::channelEnabled($channel)) {
            return false;
        }

        return match ($purpose) {
            OtpPurpose::Login => $user !== null,
            OtpPurpose::Register => $user === null,
            OtpPurpose::ResetPassword => $user !== null && AuthMethods::allowsPhonePasswordReset(),
            OtpPurpose::VerifyEmail, OtpPurpose::VerifyPhone => false,
        };
    }
}
