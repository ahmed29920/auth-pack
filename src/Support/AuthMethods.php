<?php

declare(strict_types=1);

namespace AhmedAshraf\Auth\Support;

final class AuthMethods
{
    public static function all(): array
    {
        return config('auth-package.methods', []);
    }

    public static function enabled(string $method): bool
    {
        return (bool) (self::all()[$method] ?? false);
    }

    public static function allowsEmail(): bool
    {
        return self::enabled('email_password') || self::enabled('email_otp');
    }

    public static function allowsPhone(): bool
    {
        return self::enabled('phone_password') || self::enabled('phone_otp');
    }

    public static function allowsPasswordLogin(): bool
    {
        return self::enabled('email_password') || self::enabled('phone_password');
    }

    public static function allowsOtpLogin(): bool
    {
        return self::enabled('email_otp') || self::enabled('phone_otp');
    }

    public static function showLoginModeTabs(): bool
    {
        return self::allowsPasswordLogin() && self::allowsOtpLogin();
    }

    public static function emailForPassword(): bool
    {
        return self::enabled('email_password');
    }

    public static function phoneForPassword(): bool
    {
        return self::enabled('phone_password');
    }

    public static function emailForOtp(): bool
    {
        return self::enabled('email_otp');
    }

    public static function phoneForOtp(): bool
    {
        return self::enabled('phone_otp');
    }

    public static function allowsEmailPasswordReset(): bool
    {
        return self::enabled('email_password');
    }

    public static function allowsPhonePasswordReset(): bool
    {
        return self::enabled('phone_password');
    }
}
