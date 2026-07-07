<?php

declare(strict_types=1);

namespace Ashtech\LaravelAuthKit\Support;

use Ashtech\LaravelAuthKit\Models\User;

final class VerificationRequirements
{
    public static function emailRequired(): bool
    {
        return (bool) config('laravel-auth-kit.verification.email_required', false);
    }

    public static function phoneRequired(): bool
    {
        return (bool) config('laravel-auth-kit.verification.phone_required', false);
    }

    public static function userNeedsEmailVerification(User $user): bool
    {
        return self::emailRequired()
            && $user->email
            && ! $user->isVerifiedEmail();
    }

    public static function userNeedsPhoneVerification(User $user): bool
    {
        return self::phoneRequired()
            && $user->phone
            && ! $user->isVerifiedPhone();
    }

    public static function userNeedsVerification(User $user): bool
    {
        return self::userNeedsEmailVerification($user)
            || self::userNeedsPhoneVerification($user);
    }

    /**
     * @return list<string>
     */
    public static function pendingChannels(User $user): array
    {
        $pending = [];

        if (self::userNeedsEmailVerification($user)) {
            $pending[] = 'email';
        }

        if (self::userNeedsPhoneVerification($user)) {
            $pending[] = 'phone';
        }

        return $pending;
    }
}
