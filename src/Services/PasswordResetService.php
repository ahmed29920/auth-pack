<?php

declare(strict_types=1);

namespace Ashtech\LaravelAuthKit\Services;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Ashtech\LaravelAuthKit\Contracts\UserRepositoryInterface;
use Ashtech\LaravelAuthKit\Enums\OtpChannel;
use Ashtech\LaravelAuthKit\Enums\OtpPurpose;
use Ashtech\LaravelAuthKit\Exceptions\AuthenticationException;
use Ashtech\LaravelAuthKit\Models\User;

class PasswordResetService
{
    public function __construct(
        protected UserRepositoryInterface $userRepository,
        protected OtpService $otpService,
    ) {}

    public function sendResetLink(string $email): string
    {
        $status = Password::sendResetLink(['email' => $email]);

        if ($status !== Password::RESET_LINK_SENT) {
            throw new AuthenticationException(__($status));
        }

        return __($status);
    }

    public function resetWithToken(array $data): string
    {
        $status = Password::reset(
            $data,
            function (User $user, string $password) {
                $user->forceFill(['password' => Hash::make($password)])->save();
            }
        );

        if ($status !== Password::PASSWORD_RESET) {
            throw new AuthenticationException(__($status));
        }

        return __($status);
    }

    public function sendPhoneOtp(string $phone): void
    {
        $user = $this->userRepository->findByPhone($phone);

        if (! $user) {
            return;
        }

        $this->otpService->send($phone, OtpChannel::Phone, OtpPurpose::ResetPassword, $user);
    }

    public function resetWithPhoneOtp(string $phone, string $code, string $password): User
    {
        $this->otpService->verify($phone, OtpChannel::Phone, OtpPurpose::ResetPassword, $code);

        $user = $this->userRepository->findByPhone($phone);

        if (! $user) {
            throw new AuthenticationException('User not found.');
        }

        return $this->userRepository->update($user, ['password' => $password]);
    }
}
