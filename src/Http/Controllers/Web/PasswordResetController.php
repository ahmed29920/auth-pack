<?php

declare(strict_types=1);

namespace Ashtech\LaravelAuthKit\Http\Controllers\Web;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Validation\ValidationException;
use Ashtech\LaravelAuthKit\Http\Requests\Api\ForgotPasswordRequest;
use Ashtech\LaravelAuthKit\Http\Requests\Api\ResetPasswordRequest;
use Ashtech\LaravelAuthKit\Services\PasswordResetService;
use Ashtech\LaravelAuthKit\Support\AuthMethods;

class PasswordResetController extends Controller
{
    public function __construct(
        protected PasswordResetService $passwordResetService,
    ) {}

    public function createForgot()
    {
        return view('laravel-auth-kit::auth.password.forgot');
    }

    public function storeForgot(ForgotPasswordRequest $request)
    {
        $data = $request->validated();

        if (! empty($data['email']) && AuthMethods::allowsEmailPasswordReset()) {
            $message = $this->passwordResetService->sendResetLink($data['email']);

            return back()->with('status', __($message));
        }

        if (! empty($data['phone']) && AuthMethods::allowsPhonePasswordReset()) {
            $this->passwordResetService->sendPhoneOtp($data['phone']);

            return redirect()
                ->route('auth-kit.password.reset-phone', ['phone' => $data['phone']])
                ->with('status', __('laravel-auth-kit::auth.forgot.otp_sent'));
        }

        throw ValidationException::withMessages([
            'email' => [__('laravel-auth-kit::auth.forgot.invalid_method')],
        ]);
    }

    public function createReset(Request $request, string $token)
    {
        if (! AuthMethods::allowsEmailPasswordReset()) {
            abort(404);
        }

        return view('laravel-auth-kit::auth.password.reset', [
            'token' => $token,
            'email' => $request->query('email', ''),
        ]);
    }

    public function storeReset(ResetPasswordRequest $request)
    {
        try {
            $message = $this->passwordResetService->resetWithToken($request->validated());
        } catch (\Exception $e) {
            throw ValidationException::withMessages([
                'email' => [$e->getMessage()],
            ]);
        }

        return redirect()
            ->route('auth-kit.login')
            ->with('status', __($message));
    }

    public function createResetPhone(Request $request)
    {
        if (! AuthMethods::allowsPhonePasswordReset()) {
            abort(404);
        }

        $phone = $request->query('phone', session('reset_phone'));

        if (! $phone) {
            return redirect()->route('auth-kit.password.forgot');
        }

        return view('laravel-auth-kit::auth.password.reset-phone', [
            'phone' => $phone,
        ]);
    }

    public function storeResetPhone(ResetPasswordRequest $request)
    {
        $data = $request->validated();

        try {
            $this->passwordResetService->resetWithPhoneOtp(
                $data['phone'],
                $data['otp_code'],
                $data['password'],
            );
        } catch (\Exception $e) {
            throw ValidationException::withMessages([
                'otp_code' => [$e->getMessage()],
            ]);
        }

        return redirect()
            ->route('auth-kit.login')
            ->with('status', __('laravel-auth-kit::auth.reset.password_changed'));
    }
}
