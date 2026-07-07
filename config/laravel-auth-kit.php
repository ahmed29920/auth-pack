<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | User model
    |--------------------------------------------------------------------------
    */
    'user_model' => env('AUTH_KIT_USER_MODEL', 'Ashtech\\LaravelAuthKit\\Models\\User'),

    /*
    |--------------------------------------------------------------------------
    | Role driver: enum | spatie
    |--------------------------------------------------------------------------
    | enum   → uses `role` column on users table (default, no extra packages)
    | spatie → uses spatie/laravel-permission (install in host project)
    */
    'role_driver' => env('AUTH_KIT_ROLE_DRIVER', 'enum'),

    'roles' => [
        'super_admin',
        'admin',
        'vendor',
        'vendor_staff',
        'customer',
        'delivery',
    ],

    'default_role' => 'customer',

    /*
    |--------------------------------------------------------------------------
    | Self-registration roles
    |--------------------------------------------------------------------------
    | By default only `customer` can register. Set AUTH_KIT_VENDOR_REGISTRATION=true
    | to allow public vendor sign-up.
    */
    'vendor_registration_enabled' => filter_var(env('AUTH_KIT_VENDOR_REGISTRATION', false), FILTER_VALIDATE_BOOLEAN),

    'registration_allowed_roles' => (static function (): array {
        $roles = ['customer'];

        if (filter_var(env('AUTH_KIT_VENDOR_REGISTRATION', false), FILTER_VALIDATE_BOOLEAN)) {
            $roles[] = 'vendor';
        }

        return $roles;
    })(),

    /*
    |--------------------------------------------------------------------------
    | Translatable locales (Spatie laravel-translatable)
    |--------------------------------------------------------------------------
    */
    'translatable_locales' => ['en', 'ar'],
    'fallback_locale' => env('APP_FALLBACK_LOCALE', 'en'),

    /*
    |--------------------------------------------------------------------------
    | Auth methods (config-driven)
    |--------------------------------------------------------------------------
    */
    'methods' => [
        'email_password' => filter_var(env('AUTH_KIT_EMAIL_PASSWORD', false), FILTER_VALIDATE_BOOLEAN),
        'phone_password' => filter_var(env('AUTH_KIT_PHONE_PASSWORD', true), FILTER_VALIDATE_BOOLEAN),
        'phone_otp' => filter_var(env('AUTH_KIT_PHONE_OTP', false), FILTER_VALIDATE_BOOLEAN),
        'email_otp' => filter_var(env('AUTH_KIT_EMAIL_OTP', false), FILTER_VALIDATE_BOOLEAN),
    ],

    /*
    |--------------------------------------------------------------------------
    | Clients
    |--------------------------------------------------------------------------
    */
    'clients' => [
        'api' => true,
        'web' => true,
    ],

    'api' => [
        'prefix' => 'api',
        'version' => 'v1',
        'middleware' => ['api'],
    ],

    'web' => [
        'prefix' => 'auth',
        'middleware' => ['web'],
        'redirect_after_logout' => '/auth/login',
    ],

    /*
    |--------------------------------------------------------------------------
    | Role-based redirects (web)
    |--------------------------------------------------------------------------
    */
    'redirects' => [
        'default' => '/',
        'roles' => [
            'customer' => '/shop',
            'vendor' => '/vendor/dashboard',
            'vendor_staff' => '/vendor/dashboard',
            'admin' => '/admin',
            'super_admin' => '/admin',
            'delivery' => '/delivery',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Locales (guest auth pages language switcher)
    |--------------------------------------------------------------------------
    */
    'available_locales' => [
        'ar' => 'العربية',
        'en' => 'English',
    ],

    /*
    |--------------------------------------------------------------------------
    | OTP
    |--------------------------------------------------------------------------
    */
    'otp' => [
        'length' => 6,
        'expires_minutes' => 10,
        'max_attempts' => 5,
        'throttle_seconds' => (int) env('AUTH_KIT_OTP_THROTTLE_SECONDS', 60),
        'throttle_max_attempts' => (int) env('AUTH_KIT_OTP_THROTTLE_MAX', 1),
    ],

    /*
    |--------------------------------------------------------------------------
    | SMS (swappable per project)
    |--------------------------------------------------------------------------
    | Set AUTH_KIT_SMS_SENDER to your class implementing SmsSenderInterface.
    | Default logs OTP codes when no provider is configured.
    */
    'sms' => [
        'sender' => env('AUTH_KIT_SMS_SENDER', \Ashtech\LaravelAuthKit\Services\Sms\LogSmsSender::class),
        'log_channel' => env('AUTH_KIT_SMS_LOG_CHANNEL', 'stack'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Verification
    |--------------------------------------------------------------------------
    */
    'verification' => [
        'email_required' => filter_var(env('AUTH_KIT_EMAIL_VERIFICATION_REQUIRED', true), FILTER_VALIDATE_BOOLEAN),
        'phone_required' => filter_var(env('AUTH_KIT_PHONE_VERIFICATION_REQUIRED', true), FILTER_VALIDATE_BOOLEAN),
    ],

    /*
    |--------------------------------------------------------------------------
    | Sanctum
    |--------------------------------------------------------------------------
    */
    'sanctum' => [
        'token_name' => 'auth_token',
        'abilities' => ['*'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Spatie Permission (when role_driver = spatie)
    |--------------------------------------------------------------------------
    */
    'spatie' => [
        'guard_name' => 'web',
    ],

];
