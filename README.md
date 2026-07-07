# Ashtech Laravel Auth Kit

Starter-ready authentication for **Laravel 11–13** — Blade UI, REST API, roles, OTP, and email/phone verification.

**Packagist:** [`ashtech/laravel-auth-kit`](https://packagist.org/packages/ashtech/laravel-auth-kit)

```bash
composer require ashtech/laravel-auth-kit
```

> **Note:** Legacy packages [`kango/auth`](https://packagist.org/packages/kango/auth) (`Kango\Auth`) remain for older projects. This package uses the `Ashtech\LaravelAuthKit` namespace.

## Requirements

- PHP `^8.3`
- Laravel `^11.0`, `^12.0`, or `^13.0`
- Node.js & npm (host app — required for Blade UI assets via Vite)
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
composer require ashtech/laravel-auth-kit
```

### From a private Git repository

Add the repository to the host app `composer.json`, then require the package:

```json
"repositories": [
    {
        "type": "vcs",
        "url": "https://github.com/ashtech/laravel-auth-kit"
    }
],
"minimum-stability": "stable",
"prefer-stable": true
```

```bash
composer require ashtech/laravel-auth-kit:^1.0
```

Use a tagged release (e.g. `v1.0.0`). The `@dev` constraint is only for local monorepo development.

### Migrating from legacy packages

| Legacy package | Old namespace | New package |
|----------------|---------------|-------------|
| `kango/auth` | `Kango\Auth` | `ashtech/laravel-auth-kit` |
| `ahmed-ashraf/auth` | `AhmedAshraf\Auth` | `ashtech/laravel-auth-kit` |

In host apps:

1. `composer require ashtech/laravel-auth-kit`
2. Update imports to `use Ashtech\LaravelAuthKit\...`
3. Extend `Ashtech\LaravelAuthKit\Models\User`
4. Rename env vars from `AUTH_PACKAGE_*` to `AUTH_KIT_*`
5. Publish config tag `laravel-auth-kit-config` if you published the old config

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
php artisan vendor:publish --tag=laravel-auth-kit-config
```

Available publish tags:

| Tag | Purpose |
|-----|---------|
| `laravel-auth-kit-config` | `config/laravel-auth-kit.php` |
| `laravel-auth-kit-migrations` | Copy migrations into `database/migrations` (only if you need to customize them) |
| `laravel-auth-kit-views` | Copy Blade views into `resources/views/vendor/laravel-auth-kit` |

By default, migrations and views are loaded from the package — publishing is optional.

### 3. Database migrations

The package registers migrations that extend a standard Laravel app:

- **users** — adds `phone`, `role`, `vendor_id`, `phone_verified_at`, `is_active`, soft deletes (works with Laravel’s default `users` table)
- **otps** — hashed OTP codes
- **password_reset_tokens** — skipped automatically if Laravel already created it

No need to remove Laravel’s default migrations. Run:

```bash
php artisan migrate
```

### 4. User model

Create or update `app/Models/User.php`:

```php
<?php

namespace App\Models;

use Ashtech\LaravelAuthKit\Models\User as BaseUser;

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
AUTH_KIT_USER_MODEL=App\Models\User
AUTH_KIT_ROLE_DRIVER=enum

# Registration (vendor self-sign-up is disabled by default)
# AUTH_KIT_VENDOR_REGISTRATION=true

# Auth methods (enable what you need)
AUTH_KIT_EMAIL_PASSWORD=false
AUTH_KIT_PHONE_PASSWORD=true
AUTH_KIT_PHONE_OTP=false
AUTH_KIT_EMAIL_OTP=false

# Verification gates (API + web)
AUTH_KIT_EMAIL_VERIFICATION_REQUIRED=true
AUTH_KIT_PHONE_VERIFICATION_REQUIRED=true

# OTP rate limiting
AUTH_KIT_OTP_THROTTLE_SECONDS=60
AUTH_KIT_OTP_THROTTLE_MAX=1

# Optional: custom SMS driver (must implement Ashtech\LaravelAuthKit\Contracts\SmsSenderInterface)
# AUTH_KIT_SMS_SENDER=App\\Services\\YourSmsSender
```

### 7. Middleware (`bootstrap/app.php`)

Register the verification middleware alias and optional guest redirect:

```php
use Ashtech\LaravelAuthKit\Http\Middleware\EnsureVerified;

->withMiddleware(function (Middleware $middleware): void {
    $middleware->alias([
        'verified' => EnsureVerified::class,
    ]);

    $middleware->redirectGuestsTo(fn () => route('auth-kit.login'));
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

### 9. Frontend assets (Web UI)

The package Blade views use your host app’s Vite entry points (`resources/css/app.css`, `resources/js/app.js`) and Tailwind CSS classes. Build assets before visiting `/auth`:

```bash
npm install
npm run build
```

For local development with hot reload:

```bash
npm run dev
```

> Skip this step if you disabled the web client (`laravel-auth-kit.clients.web = false`) and use the API only.

---

## Configuration

Main file: `config/laravel-auth-kit.php` (merged automatically; publish to override).

| Key | Env | Description |
|-----|-----|-------------|
| `user_model` | `AUTH_KIT_USER_MODEL` | Eloquent user class |
| `role_driver` | `AUTH_KIT_ROLE_DRIVER` | `enum` or `spatie` |
| `vendor_registration_enabled` | `AUTH_KIT_VENDOR_REGISTRATION` | Allow public vendor sign-up (default: `false`) |
| `registration_allowed_roles` | — | Derived from vendor registration flag (`customer` only by default) |
| `methods.email_password` | `AUTH_KIT_EMAIL_PASSWORD` | Email + password login |
| `methods.phone_password` | `AUTH_KIT_PHONE_PASSWORD` | Phone + password login |
| `methods.phone_otp` | `AUTH_KIT_PHONE_OTP` | Phone OTP flows |
| `methods.email_otp` | `AUTH_KIT_EMAIL_OTP` | Email OTP flows |
| `verification.email_required` | `AUTH_KIT_EMAIL_VERIFICATION_REQUIRED` | Block until email verified |
| `verification.phone_required` | `AUTH_KIT_PHONE_VERIFICATION_REQUIRED` | Block until phone verified |
| `sms.sender` | `AUTH_KIT_SMS_SENDER` | SMS implementation class |
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
AUTH_KIT_ROLE_DRIVER=spatie
```

```php
use Ashtech\LaravelAuthKit\Models\User as BaseUser;
use Spatie\Permission\Traits\HasRoles;

class User extends BaseUser
{
    use HasRoles;
}
```

Run Spatie’s migrations and seed roles that match `config('laravel-auth-kit.roles')`.

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

Prefix: `/auth` (configurable via `laravel-auth-kit.web.prefix`).

| Method | Path | Route name | Access |
|--------|------|------------|--------|
| GET | `/locale/{locale}` | `auth-kit.locale` | Guest |
| GET | `/register` | `auth-kit.register` | Guest |
| POST | `/register` | `auth-kit.register.store` | Guest |
| GET | `/login` | `auth-kit.login` | Guest |
| POST | `/login` | — | Guest |
| GET | `/password/forgot` | `auth-kit.password.forgot` | Guest |
| POST | `/password/forgot` | `auth-kit.password.forgot.store` | Guest |
| GET | `/password/reset/{token}` | `auth-kit.password.reset` | Guest |
| POST | `/password/reset` | `auth-kit.password.reset.store` | Guest |
| GET | `/password/reset-phone` | `auth-kit.password.reset-phone` | Guest |
| POST | `/password/reset-phone` | `auth-kit.password.reset-phone.store` | Guest |
| GET | `/verify` | `auth-kit.verify` | Auth |
| POST | `/verify/email` | `auth-kit.verify.email` | Auth |
| POST | `/verify/phone` | `auth-kit.verify.phone` | Auth |
| POST | `/verify/email/resend` | `auth-kit.verify.email.resend` | Auth |
| POST | `/verify/phone/resend` | `auth-kit.verify.phone.resend` | Auth |
| GET | `/profile` | `auth-kit.profile` | Auth + verified |
| POST | `/logout` | `auth-kit.logout` | Auth |

Role-based redirects after login are configured under `laravel-auth-kit.redirects.roles`.

---

## Custom SMS provider

Implement `Ashtech\LaravelAuthKit\Contracts\SmsSenderInterface` and register the class:

```env
AUTH_KIT_SMS_SENDER=App\\Services\\YourSmsSender
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
- [ ] `php artisan migrate` completed (package extends default Laravel tables)
- [ ] `App\Models\User` extends package base user
- [ ] `config/auth.php` provider uses `App\Models\User`
- [ ] `.env` auth method and verification flags set for your product
- [ ] `AUTH_KIT_VENDOR_REGISTRATION` set intentionally (disabled by default)
- [ ] `AUTH_KIT_SMS_SENDER` configured (do not rely on log driver in production)
- [ ] `verified` middleware registered; guest redirect configured if needed
- [ ] `npm install` and `npm run build` run in the host app (Web UI)
- [ ] `php artisan config:cache` run in deployment pipeline after env is set
- [ ] HTTPS enabled for session cookies and Sanctum tokens

---

## License

MIT — see [LICENSE](LICENSE).
