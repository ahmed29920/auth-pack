<?php

declare(strict_types=1);

namespace Ashtech\LaravelAuthKit\Services;

use Ashtech\LaravelAuthKit\Contracts\UserRepositoryInterface;
use Ashtech\LaravelAuthKit\Enums\OtpChannel;
use Ashtech\LaravelAuthKit\Enums\OtpPurpose;
use Ashtech\LaravelAuthKit\Models\User;

class VerificationService
{
    public function __construct(
        protected UserRepositoryInterface $userRepository,
        protected OtpService $otpService,
    ) {}

    public function sendEmailVerification(User $user): void
    {
        if (! $user->email) {
            throw new \InvalidArgumentException('User has no email address.');
        }

        $this->otpService->generate($user->email, OtpChannel::Email, OtpPurpose::VerifyEmail, $user);
    }

    public function verifyEmail(User $user, string $code): User
    {
        if (! $user->email) {
            throw new \InvalidArgumentException('User has no email address.');
        }

        $this->otpService->verify($user->email, OtpChannel::Email, OtpPurpose::VerifyEmail, $code);

        return $this->userRepository->markEmailVerified($user);
    }

    public function sendPhoneVerification(User $user): void
    {
        if (! $user->phone) {
            throw new \InvalidArgumentException('User has no phone number.');
        }

        $this->otpService->generate($user->phone, OtpChannel::Phone, OtpPurpose::VerifyPhone, $user);
    }

    public function verifyPhone(User $user, string $code): User
    {
        if (! $user->phone) {
            throw new \InvalidArgumentException('User has no phone number.');
        }

        $this->otpService->verify($user->phone, OtpChannel::Phone, OtpPurpose::VerifyPhone, $code);

        return $this->userRepository->markPhoneVerified($user);
    }

    public function dispatchPendingVerifications(User $user): void
    {
        if ($user->needsEmailVerification()) {
            $this->sendEmailVerification($user);
        }

        if ($user->needsPhoneVerification()) {
            $this->sendPhoneVerification($user);
        }
    }
}
