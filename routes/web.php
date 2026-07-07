<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Ashtech\LaravelAuthKit\Http\Controllers\Web\LocaleController;
use Ashtech\LaravelAuthKit\Http\Controllers\Web\LoginController;
use Ashtech\LaravelAuthKit\Http\Controllers\Web\PasswordResetController;
use Ashtech\LaravelAuthKit\Http\Controllers\Web\ProfileController;
use Ashtech\LaravelAuthKit\Http\Controllers\Web\RegisterController;
use Ashtech\LaravelAuthKit\Http\Controllers\Web\VerificationController;
use Ashtech\LaravelAuthKit\Http\Middleware\EnsureUserIsActive;
use Ashtech\LaravelAuthKit\Http\Middleware\EnsureVerified;
use Ashtech\LaravelAuthKit\Http\Middleware\SetLocale;

$prefix = config('laravel-auth-kit.web.prefix', 'auth');

Route::prefix($prefix)->middleware(array_merge(config('laravel-auth-kit.web.middleware', ['web']), [SetLocale::class]))->group(function () {
    Route::get('locale/{locale}', LocaleController::class)->name('auth-kit.locale');
    Route::middleware('guest')->group(function () {
        Route::get('register', [RegisterController::class, 'create'])->name('auth-kit.register');
        Route::post('register', [RegisterController::class, 'store'])->name('auth-kit.register.store');
        Route::get('login', [LoginController::class, 'create'])->name('auth-kit.login');
        Route::post('login', [LoginController::class, 'store']);

        Route::get('password/forgot', [PasswordResetController::class, 'createForgot'])->name('auth-kit.password.forgot');
        Route::post('password/forgot', [PasswordResetController::class, 'storeForgot'])->middleware('throttle:auth-otp')->name('auth-kit.password.forgot.store');
        Route::get('password/reset/{token}', [PasswordResetController::class, 'createReset'])->name('auth-kit.password.reset');
        Route::post('password/reset', [PasswordResetController::class, 'storeReset'])->name('auth-kit.password.reset.store');
        Route::get('password/reset-phone', [PasswordResetController::class, 'createResetPhone'])->name('auth-kit.password.reset-phone');
        Route::post('password/reset-phone', [PasswordResetController::class, 'storeResetPhone'])->name('auth-kit.password.reset-phone.store');
    });

    Route::middleware(['auth', EnsureUserIsActive::class])->group(function () {
        Route::get('verify', [VerificationController::class, 'show'])->name('auth-kit.verify');
        Route::post('verify/email', [VerificationController::class, 'verifyEmail'])->name('auth-kit.verify.email');
        Route::post('verify/phone', [VerificationController::class, 'verifyPhone'])->name('auth-kit.verify.phone');
        Route::post('verify/email/resend', [VerificationController::class, 'resendEmail'])->middleware('throttle:auth-otp')->name('auth-kit.verify.email.resend');
        Route::post('verify/phone/resend', [VerificationController::class, 'resendPhone'])->middleware('throttle:auth-otp')->name('auth-kit.verify.phone.resend');

        Route::middleware(EnsureVerified::class)->group(function () {
            Route::get('profile', [ProfileController::class, 'show'])->name('auth-kit.profile');
        });

        Route::post('logout', [LoginController::class, 'destroy'])->name('auth-kit.logout');
    });
});
