<?php

declare(strict_types=1);

namespace AhmedAshraf\Auth\Support;

use AhmedAshraf\Auth\Enums\OtpChannel;
use AhmedAshraf\Auth\Enums\OtpPurpose;
use AhmedAshraf\Auth\Models\User;

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
