<?php

declare(strict_types=1);

namespace AhmedAshraf\Auth\Http\Controllers\Api;

use Illuminate\Routing\Controller;
use AhmedAshraf\Auth\Http\Requests\Api\ForgotPasswordRequest;
use AhmedAshraf\Auth\Http\Requests\Api\ResetPasswordRequest;
use AhmedAshraf\Auth\Services\PasswordResetService;
use AhmedAshraf\Auth\Support\ApiResponse;
use AhmedAshraf\Auth\Support\AuthMethods;

class PasswordResetController extends Controller
{
    public function __construct(
        protected PasswordResetService $passwordResetService,
    ) {}

    public function forgot(ForgotPasswordRequest $request)
    {
        $data = $request->validated();

        if (! empty($data['email']) && AuthMethods::allowsEmailPasswordReset()) {
            $message = $this->passwordResetService->sendResetLink($data['email']);

            return ApiResponse::success(null, $message);
        }

        if (! empty($data['phone']) && AuthMethods::allowsPhonePasswordReset()) {
            $this->passwordResetService->sendPhoneOtp($data['phone']);

            return ApiResponse::success(null, __('kango-auth::auth.forgot.otp_sent'));
        }

        return ApiResponse::error(__('kango-auth::auth.forgot.invalid_method'), 422);
    }

    public function reset(ResetPasswordRequest $request)
    {
        $data = $request->validated();

        if (! empty($data['otp_code'])) {
            $this->passwordResetService->resetWithPhoneOtp(
                $data['phone'],
                $data['otp_code'],
                $data['password'],
            );

            return ApiResponse::success(null, 'Password reset successfully');
        }

        $message = $this->passwordResetService->resetWithToken($data);

        return ApiResponse::success(null, $message);
    }
}
