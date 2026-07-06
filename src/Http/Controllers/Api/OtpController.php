<?php

declare(strict_types=1);

namespace AhmedAshraf\Auth\Http\Controllers\Api;

use Illuminate\Routing\Controller;
use AhmedAshraf\Auth\Contracts\UserRepositoryInterface;
use AhmedAshraf\Auth\Enums\OtpChannel;
use AhmedAshraf\Auth\Enums\OtpPurpose;
use AhmedAshraf\Auth\Http\Requests\Api\SendOtpRequest;
use AhmedAshraf\Auth\Http\Requests\Api\VerifyOtpRequest;
use AhmedAshraf\Auth\Services\AuthService;
use AhmedAshraf\Auth\Services\OtpService;
use AhmedAshraf\Auth\Support\ApiResponse;
use AhmedAshraf\Auth\Support\OtpSendValidator;

class OtpController extends Controller
{
    public function __construct(
        protected OtpService $otpService,
        protected UserRepositoryInterface $userRepository,
        protected AuthService $authService,
    ) {}

    public function send(SendOtpRequest $request)
    {
        $data = $request->validated();
        $purpose = OtpPurpose::from($data['purpose']);
        $channel = isset($data['phone']) ? OtpChannel::Phone : OtpChannel::Email;
        $identifier = $data['phone'] ?? $data['email'];

        $user = $channel === OtpChannel::Phone
            ? $this->userRepository->findByPhone($identifier)
            : $this->userRepository->findByEmail($identifier);

        $this->otpService->send($identifier, $channel, $purpose, $user);

        return ApiResponse::success(null, 'OTP sent successfully');
    }

    public function verify(VerifyOtpRequest $request)
    {
        $data = $request->validated();
        $purpose = OtpPurpose::from($data['purpose']);
        $channel = isset($data['phone']) ? OtpChannel::Phone : OtpChannel::Email;
        $identifier = $data['phone'] ?? $data['email'];

        if ($purpose === OtpPurpose::Login) {
            if (! OtpSendValidator::channelEnabled($channel)) {
                return ApiResponse::error(__('kango-auth::auth.otp.channel_disabled'), 422);
            }

            $result = $this->authService->login([
                $channel === OtpChannel::Phone ? 'phone' : 'email' => $identifier,
                'otp_code' => $data['code'],
            ], 'api');

            return ApiResponse::success($result, 'Login successful');
        }

        $this->otpService->verify($identifier, $channel, $purpose, $data['code']);

        return ApiResponse::success(null, 'OTP verified successfully');
    }
}
