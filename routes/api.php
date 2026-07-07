<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Ashtech\LaravelAuthKit\Http\Controllers\Api\LoginController;
use Ashtech\LaravelAuthKit\Http\Controllers\Api\OtpController;
use Ashtech\LaravelAuthKit\Http\Controllers\Api\PasswordResetController;
use Ashtech\LaravelAuthKit\Http\Controllers\Api\ProfileController;
use Ashtech\LaravelAuthKit\Http\Controllers\Api\RegisterController;
use Ashtech\LaravelAuthKit\Http\Controllers\Api\VerificationController;
use Ashtech\LaravelAuthKit\Http\Middleware\EnsureUserIsActive;
use Ashtech\LaravelAuthKit\Http\Middleware\EnsureVerified;

$version = config('laravel-auth-kit.api.version', 'v1');

Route::prefix($version)->group(function () {
    Route::prefix('auth')->group(function () {
        Route::post('register', [RegisterController::class, 'store']);
        Route::post('login', [LoginController::class, 'store']);
        Route::post('otp/send', [OtpController::class, 'send'])->middleware('throttle:auth-otp');
        Route::post('otp/verify', [OtpController::class, 'verify']);
        Route::post('password/forgot', [PasswordResetController::class, 'forgot'])->middleware('throttle:auth-otp');
        Route::post('password/reset', [PasswordResetController::class, 'reset']);

        Route::middleware(['auth:sanctum', EnsureUserIsActive::class])->group(function () {
            Route::post('logout', [LoginController::class, 'destroy']);
            Route::post('email/send-verification', [VerificationController::class, 'sendEmail'])->middleware('throttle:auth-otp');
            Route::post('email/verify', [VerificationController::class, 'verifyEmail']);
            Route::post('phone/send-verification', [VerificationController::class, 'sendPhone'])->middleware('throttle:auth-otp');
            Route::post('phone/verify', [VerificationController::class, 'verifyPhone']);

            Route::middleware(EnsureVerified::class)->group(function () {
                Route::get('me', [ProfileController::class, 'show']);
            });
        });
    });
});
