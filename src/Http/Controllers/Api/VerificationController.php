<?php

declare(strict_types=1);

namespace AhmedAshraf\Auth\Http\Controllers\Api;

use Illuminate\Routing\Controller;
use AhmedAshraf\Auth\Http\Requests\Api\VerifyContactRequest;
use AhmedAshraf\Auth\Http\Resources\UserResource;
use AhmedAshraf\Auth\Services\VerificationService;
use AhmedAshraf\Auth\Support\ApiResponse;

class VerificationController extends Controller
{
    public function __construct(
        protected VerificationService $verificationService,
    ) {}

    public function sendEmail()
    {
        $this->verificationService->sendEmailVerification(request()->user());

        return ApiResponse::success(null, 'Verification code sent to your email');
    }

    public function verifyEmail(VerifyContactRequest $request)
    {
        $user = $this->verificationService->verifyEmail(
            request()->user(),
            $request->validated('code'),
        );

        return ApiResponse::success(['user' => new UserResource($user)], 'Email verified successfully');
    }

    public function sendPhone()
    {
        $this->verificationService->sendPhoneVerification(request()->user());

        return ApiResponse::success(null, 'Verification code sent to your phone');
    }

    public function verifyPhone(VerifyContactRequest $request)
    {
        $user = $this->verificationService->verifyPhone(
            request()->user(),
            $request->validated('code'),
        );

        return ApiResponse::success(['user' => new UserResource($user)], 'Phone verified successfully');
    }
}
