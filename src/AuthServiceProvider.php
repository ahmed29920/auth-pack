<?php

declare(strict_types=1);

namespace Ashtech\LaravelAuthKit;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Ashtech\LaravelAuthKit\Contracts\OtpRepositoryInterface;
use Ashtech\LaravelAuthKit\Contracts\SmsSenderInterface;
use Ashtech\LaravelAuthKit\Contracts\RoleManagerInterface;
use Ashtech\LaravelAuthKit\Contracts\UserRepositoryInterface;
use Ashtech\LaravelAuthKit\Exceptions\AccountInactiveException;
use Ashtech\LaravelAuthKit\Exceptions\AuthenticationException;
use Ashtech\LaravelAuthKit\Exceptions\InvalidOtpException;
use Ashtech\LaravelAuthKit\Exceptions\TooManyOtpAttemptsException;
use Ashtech\LaravelAuthKit\Mail\ResetPasswordMail;
use Ashtech\LaravelAuthKit\Services\Sms\LogSmsSender;
use Ashtech\LaravelAuthKit\Models\Otp;
use Ashtech\LaravelAuthKit\Models\User;
use Ashtech\LaravelAuthKit\Repositories\OtpRepository;
use Ashtech\LaravelAuthKit\Repositories\UserRepository;
use Ashtech\LaravelAuthKit\Services\Role\EnumRoleManager;
use Ashtech\LaravelAuthKit\Services\Role\SpatieRoleManager;
use Ashtech\LaravelAuthKit\Support\ApiResponse;

class AuthServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/laravel-auth-kit.php', 'laravel-auth-kit');

        $this->app->bind(UserRepositoryInterface::class, function ($app) {
            $model = $app->make(config('laravel-auth-kit.user_model'));

            return new UserRepository($model);
        });

        $this->app->bind(OtpRepositoryInterface::class, function ($app) {
            return new OtpRepository(new Otp);
        });

        $this->app->singleton(EnumRoleManager::class);
        $this->app->singleton(RoleManagerInterface::class, function ($app) {
            if (config('laravel-auth-kit.role_driver') === 'spatie') {
                return new SpatieRoleManager($app->make(EnumRoleManager::class));
            }

            return $app->make(EnumRoleManager::class);
        });

        $this->app->bind(SmsSenderInterface::class, function ($app) {
            $class = config('laravel-auth-kit.sms.sender', LogSmsSender::class);

            return $app->make($class);
        });
    }

    public function boot(): void
    {
        config([
            'translatable.locales' => config('laravel-auth-kit.translatable_locales', ['en', 'ar']),
            'translatable.fallback_locale' => config('laravel-auth-kit.fallback_locale', 'en'),
        ]);

        $this->publishes([
            __DIR__.'/../resources/views' => resource_path('views/vendor/laravel-auth-kit'),
        ], 'laravel-auth-kit-views');

        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'laravel-auth-kit');
        $this->loadTranslationsFrom(__DIR__.'/../lang', 'laravel-auth-kit');

        $this->registerRoutes();
        $this->registerPublishing();
        $this->registerExceptionRendering();
        $this->registerPasswordResetNotifications();
        $this->registerRateLimiters();
    }

    protected function registerPasswordResetNotifications(): void
    {
        $prefix = trim(config('laravel-auth-kit.web.prefix', 'auth'), '/');

        ResetPassword::createUrlUsing(function ($user, string $token) use ($prefix) {
            return url("/{$prefix}/password/reset/{$token}?email=".urlencode($user->getEmailForPasswordReset()));
        });

        ResetPassword::toMailUsing(function ($notifiable, string $token) use ($prefix) {
            $url = url("/{$prefix}/password/reset/{$token}?email=".urlencode($notifiable->getEmailForPasswordReset()));
            $expire = (int) config('auth.passwords.'.config('auth.defaults.passwords').'.expire', 60);

            return new ResetPasswordMail($url, $notifiable->name ?? null, $expire);
        });
    }

    protected function registerRateLimiters(): void
    {
        $decaySeconds = (int) config('laravel-auth-kit.otp.throttle_seconds', 60);
        $maxAttempts = (int) config('laravel-auth-kit.otp.throttle_max_attempts', 1);

        RateLimiter::for('auth-otp', function (Request $request) use ($decaySeconds, $maxAttempts) {
            $identifier = $request->input('phone')
                ?? $request->input('email')
                ?? $request->user()?->phone
                ?? $request->user()?->email
                ?? $request->ip();

            return Limit::perSecond($maxAttempts, $decaySeconds)
                ->by('auth-otp-route:'.$identifier)
                ->response(function (Request $request, array $headers) {
                    $seconds = (int) ($headers['Retry-After'] ?? $decaySeconds);
                    $message = __('laravel-auth-kit::auth.otp.throttled', ['seconds' => $seconds]);

                    if ($request->expectsJson() || $request->is('api/*')) {
                        return response()->json([
                            'success' => false,
                            'message' => $message,
                            'errors' => ['retry_after_seconds' => $seconds],
                        ], 429, $headers);
                    }

                    return redirect()->back()->withErrors(['otp' => $message]);
                });
        });
    }

    protected function registerRoutes(): void
    {
        if (config('laravel-auth-kit.clients.api')) {
            $apiPrefix = trim(config('laravel-auth-kit.api.prefix', 'api'), '/');
            $apiMiddleware = config('laravel-auth-kit.api.middleware', ['api']);

            Route::middleware($apiMiddleware)
                ->prefix($apiPrefix)
                ->group(__DIR__.'/../routes/api.php');
        }

        if (config('laravel-auth-kit.clients.web')) {
            Route::group([], __DIR__.'/../routes/web.php');
        }
    }

    protected function registerPublishing(): void
    {
        if (! $this->app->runningInConsole()) {
            return;
        }

        $this->publishes([
            __DIR__.'/../config/laravel-auth-kit.php' => config_path('laravel-auth-kit.php'),
        ], 'laravel-auth-kit-config');

        $this->publishes([
            __DIR__.'/../database/migrations' => database_path('migrations'),
        ], 'laravel-auth-kit-migrations');

        $this->publishes([
            __DIR__.'/../resources/views' => resource_path('views/vendor/laravel-auth-kit'),
        ], 'laravel-auth-kit-views');
    }

    protected function registerExceptionRendering(): void
    {
        if (! $this->app->bound('Illuminate\Contracts\Debug\ExceptionHandler')) {
            return;
        }

        $handler = $this->app->make('Illuminate\Contracts\Debug\ExceptionHandler');

        $renderJson = function ($request, $exception, int $status) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return ApiResponse::error($exception->getMessage(), $status);
            }

            return null;
        };

        $handler->renderable(function (AuthenticationException $e, $request) use ($renderJson) {
            return $renderJson($request, $e, 401);
        });

        $handler->renderable(function (AccountInactiveException $e, $request) use ($renderJson) {
            return $renderJson($request, $e, 403);
        });

        $handler->renderable(function (InvalidOtpException $e, $request) use ($renderJson) {
            return $renderJson($request, $e, 422);
        });

        $handler->renderable(function (TooManyOtpAttemptsException $e, $request) use ($renderJson) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return ApiResponse::error($e->getMessage(), 429, [
                    'retry_after_seconds' => $e->retryAfterSeconds,
                ]);
            }

            return redirect()->back()
                ->withInput()
                ->withErrors(['otp' => $e->getMessage()]);
        });
    }
}
