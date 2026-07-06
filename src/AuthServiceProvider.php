<?php

declare(strict_types=1);

namespace AhmedAshraf\Auth;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use AhmedAshraf\Auth\Contracts\OtpRepositoryInterface;
use AhmedAshraf\Auth\Contracts\SmsSenderInterface;
use AhmedAshraf\Auth\Contracts\RoleManagerInterface;
use AhmedAshraf\Auth\Contracts\UserRepositoryInterface;
use AhmedAshraf\Auth\Exceptions\AccountInactiveException;
use AhmedAshraf\Auth\Exceptions\AuthenticationException;
use AhmedAshraf\Auth\Exceptions\InvalidOtpException;
use AhmedAshraf\Auth\Exceptions\TooManyOtpAttemptsException;
use AhmedAshraf\Auth\Mail\ResetPasswordMail;
use AhmedAshraf\Auth\Services\Sms\LogSmsSender;
use AhmedAshraf\Auth\Models\Otp;
use AhmedAshraf\Auth\Models\User;
use AhmedAshraf\Auth\Repositories\OtpRepository;
use AhmedAshraf\Auth\Repositories\UserRepository;
use AhmedAshraf\Auth\Services\Role\EnumRoleManager;
use AhmedAshraf\Auth\Services\Role\SpatieRoleManager;
use AhmedAshraf\Auth\Support\ApiResponse;

class AuthServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/auth-package.php', 'auth-package');

        $this->app->bind(UserRepositoryInterface::class, function ($app) {
            $model = $app->make(config('auth-package.user_model'));

            return new UserRepository($model);
        });

        $this->app->bind(OtpRepositoryInterface::class, function ($app) {
            return new OtpRepository(new Otp);
        });

        $this->app->singleton(EnumRoleManager::class);
        $this->app->singleton(RoleManagerInterface::class, function ($app) {
            if (config('auth-package.role_driver') === 'spatie') {
                return new SpatieRoleManager($app->make(EnumRoleManager::class));
            }

            return $app->make(EnumRoleManager::class);
        });

        $this->app->bind(SmsSenderInterface::class, function ($app) {
            $class = config('auth-package.sms.sender', LogSmsSender::class);

            return $app->make($class);
        });
    }

    public function boot(): void
    {
        config([
            'translatable.locales' => config('auth-package.translatable_locales', ['en', 'ar']),
            'translatable.fallback_locale' => config('auth-package.fallback_locale', 'en'),
        ]);

        $this->publishes([
            __DIR__.'/../resources/views' => resource_path('views/vendor/kango-auth'),
        ], 'kango-auth-views');

        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'kango-auth');
        $this->loadTranslationsFrom(__DIR__.'/../lang', 'kango-auth');

        $this->registerRoutes();
        $this->registerPublishing();
        $this->registerExceptionRendering();
        $this->registerPasswordResetNotifications();
        $this->registerRateLimiters();
    }

    protected function registerPasswordResetNotifications(): void
    {
        $prefix = trim(config('auth-package.web.prefix', 'auth'), '/');

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
        $decaySeconds = (int) config('auth-package.otp.throttle_seconds', 60);
        $maxAttempts = (int) config('auth-package.otp.throttle_max_attempts', 1);

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
                    $message = __('kango-auth::auth.otp.throttled', ['seconds' => $seconds]);

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
        if (config('auth-package.clients.api')) {
            $apiPrefix = trim(config('auth-package.api.prefix', 'api'), '/');
            $apiMiddleware = config('auth-package.api.middleware', ['api']);

            Route::middleware($apiMiddleware)
                ->prefix($apiPrefix)
                ->group(__DIR__.'/../routes/api.php');
        }

        if (config('auth-package.clients.web')) {
            Route::group([], __DIR__.'/../routes/web.php');
        }
    }

    protected function registerPublishing(): void
    {
        if (! $this->app->runningInConsole()) {
            return;
        }

        $this->publishes([
            __DIR__.'/../config/auth-package.php' => config_path('auth-package.php'),
        ], 'kango-auth-config');

        $this->publishes([
            __DIR__.'/../database/migrations' => database_path('migrations'),
        ], 'kango-auth-migrations');

        $this->publishes([
            __DIR__.'/../resources/views' => resource_path('views/vendor/kango-auth'),
        ], 'kango-auth-views');
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
