<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use AhmedAshraf\Auth\Http\Controllers\Web\LocaleController;
use AhmedAshraf\Auth\Http\Controllers\Web\LoginController;
use AhmedAshraf\Auth\Http\Controllers\Web\PasswordResetController;
use AhmedAshraf\Auth\Http\Controllers\Web\ProfileController;
use AhmedAshraf\Auth\Http\Controllers\Web\RegisterController;
use AhmedAshraf\Auth\Http\Controllers\Web\VerificationController;
use AhmedAshraf\Auth\Http\Middleware\EnsureUserIsActive;
use AhmedAshraf\Auth\Http\Middleware\EnsureVerified;
use AhmedAshraf\Auth\Http\Middleware\SetLocale;

$prefix = config('auth-package.web.prefix', 'auth');

Route::prefix($prefix)->middleware(array_merge(config('auth-package.web.middleware', ['web']), [SetLocale::class]))->group(function () {
    Route::get('locale/{locale}', LocaleController::class)->name('kango.auth.locale');
    Route::middleware('guest')->group(function () {
        Route::get('register', [RegisterController::class, 'create'])->name('kango.auth.register');
        Route::post('register', [RegisterController::class, 'store'])->name('kango.auth.register.store');
        Route::get('login', [LoginController::class, 'create'])->name('kango.auth.login');
        Route::post('login', [LoginController::class, 'store']);

        Route::get('password/forgot', [PasswordResetController::class, 'createForgot'])->name('kango.auth.password.forgot');
        Route::post('password/forgot', [PasswordResetController::class, 'storeForgot'])->middleware('throttle:auth-otp')->name('kango.auth.password.forgot.store');
        Route::get('password/reset/{token}', [PasswordResetController::class, 'createReset'])->name('kango.auth.password.reset');
        Route::post('password/reset', [PasswordResetController::class, 'storeReset'])->name('kango.auth.password.reset.store');
        Route::get('password/reset-phone', [PasswordResetController::class, 'createResetPhone'])->name('kango.auth.password.reset-phone');
        Route::post('password/reset-phone', [PasswordResetController::class, 'storeResetPhone'])->name('kango.auth.password.reset-phone.store');
    });

    Route::middleware(['auth', EnsureUserIsActive::class])->group(function () {
        Route::get('verify', [VerificationController::class, 'show'])->name('kango.auth.verify');
        Route::post('verify/email', [VerificationController::class, 'verifyEmail'])->name('kango.auth.verify.email');
        Route::post('verify/phone', [VerificationController::class, 'verifyPhone'])->name('kango.auth.verify.phone');
        Route::post('verify/email/resend', [VerificationController::class, 'resendEmail'])->middleware('throttle:auth-otp')->name('kango.auth.verify.email.resend');
        Route::post('verify/phone/resend', [VerificationController::class, 'resendPhone'])->middleware('throttle:auth-otp')->name('kango.auth.verify.phone.resend');

        Route::middleware(EnsureVerified::class)->group(function () {
            Route::get('profile', [ProfileController::class, 'show'])->name('kango.auth.profile');
        });

        Route::post('logout', [LoginController::class, 'destroy'])->name('kango.auth.logout');
    });
});
