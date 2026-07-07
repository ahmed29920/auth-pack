<?php

declare(strict_types=1);

namespace Ashtech\LaravelAuthKit\Http\Controllers\Api;

use Illuminate\Routing\Controller;
use Ashtech\LaravelAuthKit\Http\Requests\Api\VerifyContactRequest;
use Ashtech\LaravelAuthKit\Http\Resources\UserResource;
use Ashtech\LaravelAuthKit\Services\VerificationService;
use Ashtech\LaravelAuthKit\Support\ApiResponse;

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
