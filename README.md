# Ahmed Ashraf Laravel Auth

Multi-vendor authentication for **Laravel 11–13** — Blade UI, REST API, roles, OTP, and email/phone verification.

**Packagist:** [`ahmed-ashraf/auth`](https://packagist.org/packages/ahmed-ashraf/auth)

> **Note:** This is the current package. The legacy [`kango/auth`](https://packagist.org/packages/kango/auth) release uses the `Kango\Auth` namespace and remains available for existing projects. New projects should use `ahmed-ashraf/auth` with the `AhmedAshraf\Auth` namespace.

## Requirements

- PHP `^8.3`
- Laravel `^11.0`, `^12.0`, or `^13.0`
- [laravel/sanctum](https://github.com/laravel/sanctum) `^4.0` (required dependency; host app must run Sanctum migrations)
- [spatie/laravel-translatable](https://github.com/spatie/laravel-translatable) `^6.14` (installed automatically with this package)

## Features

- Registration, login, logout (web session + API Sanctum tokens)
- Email/password and phone/password (configurable per project)
- OTP via phone or email (login, register, password reset, verification)
- Hashed OTP storage with attempt limiting
- Roles: `super_admin`, `admin`, `vendor`, `vendor_staff`, `customer`, `delivery`
- **Enum roles** (default, `role` column) or **Spatie Permission** (`role_driver=spatie`)
- Guest locale switcher on auth pages (`en` / `ar`)
- Swappable SMS provider for OTP (`SmsSenderInterface`)
- Web and API clients can be enabled or disabled independently

---

## Installing the package

### From Packagist (recommended)

```bash
composer require ahmed-ashraf/auth
```

### From a private Git repository

Add the repository to the host app `composer.json`, then require the package:

```json
"repositories": [
    {
        "type": "vcs",
        "url": "https://github.com/ahmed29920/laravel-auth"
    }
],
"minimum-stability": "stable",
"prefer-stable": true
```

```bash
composer require ahmed-ashraf/auth:^1.0
```

Use a tagged release (e.g. `v1.0.0`). The `@dev` constraint is only for local monorepo development.

### Migrating from `kango/auth`

The legacy package uses the `Kango\Auth` namespace. This package uses `AhmedAshraf\Auth`. In host apps:

1. Replace the Composer dependency: `composer require ahmed-ashraf/auth`
2. Update `use Kango\Auth\...` imports to `use AhmedAshraf\Auth\...`
3. Update `App\Models\User` to extend `AhmedAshraf\Auth\Models\User`
4. Update middleware references in `bootstrap/app.php` if you imported classes explicitly

Route names (`kango.auth.*`), Blade views (`kango-auth::`), and publish tags (`kango-auth-*`) are unchanged.

---

## Host application setup

### 1. Publish and migrate Sanctum

Sanctum is installed with this package, but the host app must publish its config/migrations:

```bash
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
php artisan migrate
```

This creates Sanctum’s `personal_access_tokens` table (required for API auth).

### 2. Publish package config (optional)

```bash
php artisan vendor:publish --tag=kango-auth-config
```

Available publish tags:

| Tag | Purpose |
|-----|---------|
| `kango-auth-config` | `config/auth-package.php` |
| `kango-auth-migrations` | Copy migrations into `database/migrations` (only if you need to customize them) |
| `kango-auth-views` | Copy Blade views into `resources/views/vendor/kango-auth` |

By default, migrations and views are loaded from the package — publishing is optional.

### 3. Users table / migrations

The package registers migrations for:

- `users` (with `role`, `phone`, `vendor_id`, verification timestamps, soft deletes)
- `otps` (hashed OTP codes)
- `password_reset_tokens` (Laravel-compatible email reset table)

> **Important:** If your Laravel app already has a default `create_users_table` or `password_reset_tokens` migration, **remove or skip the duplicate** before running `php artisan migrate`.

Then migrate:

```bash
php artisan migrate
```

### 4. User model

Create or update `app/Models/User.php`:

```php
<?php

namespace App\Models;

use AhmedAshraf\Auth\Models\User as BaseUser;

class User extends BaseUser
{
    //
}
```

### 5. Laravel `config/auth.php`

Point the user provider at your application model:

```php
use App\Models\User;

'providers' => [
    'users' => [
        'driver' => 'eloquent',
        'model' => User::class,
    ],
],
```

### 6. Environment variables

Add to `.env`:

```env
AUTH_PACKAGE_USER_MODEL=App\Models\User
AUTH_PACKAGE_ROLE_DRIVER=enum

# Registration (vendor self-sign-up is disabled by default)
# AUTH_PACKAGE_VENDOR_REGISTRATION=true

# Auth methods (enable what you need)
AUTH_PACKAGE_EMAIL_PASSWORD=false
AUTH_PACKAGE_PHONE_PASSWORD=true
AUTH_PACKAGE_PHONE_OTP=false
AUTH_PACKAGE_EMAIL_OTP=false

# Verification gates (API + web)
AUTH_PACKAGE_EMAIL_VERIFICATION_REQUIRED=true
AUTH_PACKAGE_PHONE_VERIFICATION_REQUIRED=true

# OTP rate limiting
AUTH_PACKAGE_OTP_THROTTLE_SECONDS=60
AUTH_PACKAGE_OTP_THROTTLE_MAX=1

# Optional: custom SMS driver (must implement AhmedAshraf\Auth\Contracts\SmsSenderInterface)
# AUTH_PACKAGE_SMS_SENDER=App\\Services\\YourSmsSender
```

### 7. Middleware (`bootstrap/app.php`)

Register the verification middleware alias and optional guest redirect:

```php
use AhmedAshraf\Auth\Http\Middleware\EnsureVerified;

->withMiddleware(function (Middleware $middleware): void {
    $middleware->alias([
        'verified' => EnsureVerified::class,
    ]);

    $middleware->redirectGuestsTo(fn () => route('kango.auth.login'));
})
```

Protect routes that require a verified user:

```php
Route::middleware(['auth', 'verified'])->group(function () {
    // ...
});
```

### 8. Clear config cache

```bash
php artisan config:clear
```

---

## Configuration

Main file: `config/auth-package.php` (merged automatically; publish to override).

| Key | Env | Description |
|-----|-----|-------------|
| `user_model` | `AUTH_PACKAGE_USER_MODEL` | Eloquent user class |
| `role_driver` | `AUTH_PACKAGE_ROLE_DRIVER` | `enum` or `spatie` |
| `vendor_registration_enabled` | `AUTH_PACKAGE_VENDOR_REGISTRATION` | Allow public vendor sign-up (default: `false`) |
| `registration_allowed_roles` | — | Derived from vendor registration flag (`customer` only by default) |
| `methods.email_password` | `AUTH_PACKAGE_EMAIL_PASSWORD` | Email + password login |
| `methods.phone_password` | `AUTH_PACKAGE_PHONE_PASSWORD` | Phone + password login |
| `methods.phone_otp` | `AUTH_PACKAGE_PHONE_OTP` | Phone OTP flows |
| `methods.email_otp` | `AUTH_PACKAGE_EMAIL_OTP` | Email OTP flows |
| `verification.email_required` | `AUTH_PACKAGE_EMAIL_VERIFICATION_REQUIRED` | Block until email verified |
| `verification.phone_required` | `AUTH_PACKAGE_PHONE_VERIFICATION_REQUIRED` | Block until phone verified |
| `sms.sender` | `AUTH_PACKAGE_SMS_SENDER` | SMS implementation class |
| `translatable_locales` | — | Locales for Spatie translatable (`en`, `ar`) |
| `available_locales` | — | Guest language switcher labels |
| `web.prefix` | — | URL prefix for Blade routes (default: `auth`) |
| `api.prefix` / `api.version` | — | API base: `/api/v1/...` |
| `clients.web` / `clients.api` | — | Enable or disable each client independently |

---

## Spatie Permission (optional)

```bash
composer require spatie/laravel-permission
```

```env
AUTH_PACKAGE_ROLE_DRIVER=spatie
```

```php
use AhmedAshraf\Auth\Models\User as BaseUser;
use Spatie\Permission\Traits\HasRoles;

class User extends BaseUser
{
    use HasRoles;
}
```

Run Spatie’s migrations and seed roles that match `config('auth-package.roles')`.

---

## API reference

Base URL: `/api/v1/auth`  
Protected routes require `Authorization: Bearer {token}` (Sanctum).

| Method | Endpoint | Auth |
|--------|----------|------|
| POST | `/register` | Guest |
| POST | `/login` | Guest |
| POST | `/otp/send` | Guest (throttled) |
| POST | `/otp/verify` | Guest |
| POST | `/password/forgot` | Guest (throttled) |
| POST | `/password/reset` | Guest |
| POST | `/logout` | Sanctum |
| POST | `/email/send-verification` | Sanctum (throttled) |
| POST | `/email/verify` | Sanctum |
| POST | `/phone/send-verification` | Sanctum (throttled) |
| POST | `/phone/verify` | Sanctum |
| GET | `/me` | Sanctum + verified |

JSON responses use the package’s standard success/error envelope.

OTP send always returns a generic success message to avoid user enumeration. Codes are only dispatched when the purpose and channel rules allow it (e.g. login requires an existing account; register requires a new identifier).

---

## Web routes

Prefix: `/auth` (configurable via `auth-package.web.prefix`).

| Method | Path | Route name | Access |
|--------|------|------------|--------|
| GET | `/locale/{locale}` | `kango.auth.locale` | Guest |
| GET | `/register` | `kango.auth.register` | Guest |
| POST | `/register` | `kango.auth.register.store` | Guest |
| GET | `/login` | `kango.auth.login` | Guest |
| POST | `/login` | — | Guest |
| GET | `/password/forgot` | `kango.auth.password.forgot` | Guest |
| POST | `/password/forgot` | `kango.auth.password.forgot.store` | Guest |
| GET | `/password/reset/{token}` | `kango.auth.password.reset` | Guest |
| POST | `/password/reset` | `kango.auth.password.reset.store` | Guest |
| GET | `/password/reset-phone` | `kango.auth.password.reset-phone` | Guest |
| POST | `/password/reset-phone` | `kango.auth.password.reset-phone.store` | Guest |
| GET | `/verify` | `kango.auth.verify` | Auth |
| POST | `/verify/email` | `kango.auth.verify.email` | Auth |
| POST | `/verify/phone` | `kango.auth.verify.phone` | Auth |
| POST | `/verify/email/resend` | `kango.auth.verify.email.resend` | Auth |
| POST | `/verify/phone/resend` | `kango.auth.verify.phone.resend` | Auth |
| GET | `/profile` | `kango.auth.profile` | Auth + verified |
| POST | `/logout` | `kango.auth.logout` | Auth |

Role-based redirects after login are configured under `auth-package.redirects.roles`.

---

## Custom SMS provider

Implement `AhmedAshraf\Auth\Contracts\SmsSenderInterface` and register the class:

```env
AUTH_PACKAGE_SMS_SENDER=App\\Services\\YourSmsSender
```

If unset, OTP codes are written to the log via `LogSmsSender` (local development only).

---

## Development

Run the package test suite (requires MySQL and a database named `auth_pack_test`, or override `DB_*` in `phpunit.xml`):

```bash
composer install
composer test
```

---

## Production checklist

- [ ] Package installed via Composer (Packagist or tagged VCS release)
- [ ] Sanctum migrations published and run in the host app
- [ ] No duplicate Laravel `users` migration
- [ ] `App\Models\User` extends package base user
- [ ] `config/auth.php` provider uses `App\Models\User`
- [ ] `.env` auth method and verification flags set for your product
- [ ] `AUTH_PACKAGE_VENDOR_REGISTRATION` set intentionally (disabled by default)
- [ ] `AUTH_PACKAGE_SMS_SENDER` configured (do not rely on log driver in production)
- [ ] `verified` middleware registered; guest redirect configured if needed
- [ ] `php artisan config:cache` run in deployment pipeline after env is set
- [ ] HTTPS enabled for session cookies and Sanctum tokens

---

## License

MIT — see [LICENSE](LICENSE).
