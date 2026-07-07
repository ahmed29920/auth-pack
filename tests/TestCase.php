<?php

declare(strict_types=1);

namespace Ashtech\LaravelAuthKit\Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Ashtech\LaravelAuthKit\AuthServiceProvider;
use Ashtech\LaravelAuthKit\Tests\Support\CapturingSmsSender;
use Laravel\Sanctum\SanctumServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        CapturingSmsSender::reset();
    }

    protected function getPackageProviders($app): array
    {
        return [
            AuthServiceProvider::class,
            SanctumServiceProvider::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('app.key', 'base64:'.base64_encode(random_bytes(32)));
        $app['config']->set('database.default', 'mysql');
        $app['config']->set('database.connections.mysql', [
            'driver' => 'mysql',
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '3306'),
            'database' => env('DB_DATABASE', 'auth_pack_test'),
            'username' => env('DB_USERNAME', 'root'),
            'password' => env('DB_PASSWORD', ''),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'strict' => true,
            'engine' => null,
        ]);

        $app['config']->set('laravel-auth-kit.user_model', \Ashtech\LaravelAuthKit\Models\User::class);
        $app['config']->set('laravel-auth-kit.methods.email_password', false);
        $app['config']->set('laravel-auth-kit.methods.phone_password', true);
        $app['config']->set('laravel-auth-kit.methods.phone_otp', true);
        $app['config']->set('laravel-auth-kit.methods.email_otp', false);
        $app['config']->set('laravel-auth-kit.verification.email_required', false);
        $app['config']->set('laravel-auth-kit.verification.phone_required', false);
        $app['config']->set('laravel-auth-kit.sms.sender', CapturingSmsSender::class);
        $app['config']->set('laravel-auth-kit.otp.throttle_seconds', 0);
        $app['config']->set('laravel-auth-kit.otp.throttle_max_attempts', 100);
    }

    protected function defineDatabaseMigrations(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        $this->loadMigrationsFrom(__DIR__.'/../vendor/laravel/sanctum/database/migrations');
    }

    protected function apiPrefix(string $path = ''): string
    {
        $prefix = trim(config('laravel-auth-kit.api.prefix', 'api'), '/');
        $version = trim(config('laravel-auth-kit.api.version', 'v1'), '/');
        $path = ltrim($path, '/');

        return '/'.$prefix.'/'.$version.'/auth'.($path !== '' ? '/'.$path : '');
    }
}
