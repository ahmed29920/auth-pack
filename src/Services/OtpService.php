<?php

declare(strict_types=1);

namespace AhmedAshraf\Auth\Services;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use AhmedAshraf\Auth\Contracts\OtpRepositoryInterface;
use AhmedAshraf\Auth\Contracts\SmsSenderInterface;
use AhmedAshraf\Auth\Enums\OtpChannel;
use AhmedAshraf\Auth\Enums\OtpPurpose;
use AhmedAshraf\Auth\Exceptions\InvalidOtpException;
use AhmedAshraf\Auth\Exceptions\TooManyOtpAttemptsException;
use AhmedAshraf\Auth\Mail\OtpCodeMail;
use AhmedAshraf\Auth\Models\Otp;
use AhmedAshraf\Auth\Models\User;
use AhmedAshraf\Auth\Notifications\OtpNotification;
use AhmedAshraf\Auth\Support\OtpSendValidator;

class OtpService
{
    public function __construct(
        protected OtpRepositoryInterface $otpRepository,
        protected SmsSenderInterface $smsSender,
    ) {}

    public function send(string $identifier, OtpChannel $channel, OtpPurpose $purpose, ?User $user = null): void
    {
        if (! OtpSendValidator::shouldSend($purpose, $channel, $user)) {
            return;
        }

        $this->generate($identifier, $channel, $purpose, $user);
    }

    public function generate(string $identifier, OtpChannel $channel, OtpPurpose $purpose, ?User $user = null): Otp
    {
        $this->ensureNotThrottled($identifier, $channel, $purpose);

        $this->otpRepository->invalidateForIdentifier($identifier, $channel, $purpose);

        $length = (int) config('auth-package.otp.length', 6);
        $plainCode = str_pad((string) random_int(0, (10 ** $length) - 1), $length, '0', STR_PAD_LEFT);

        $otp = $this->otpRepository->create([
            'user_id' => $user?->id,
            'identifier' => $identifier,
            'channel' => $channel,
            'code' => Hash::make($plainCode),
            'purpose' => $purpose,
            'expires_at' => now()->addMinutes((int) config('auth-package.otp.expires_minutes', 10)),
        ]);

        $this->dispatch($otp, $user, $plainCode);

        return $otp;
    }

    public function verify(string $identifier, OtpChannel $channel, OtpPurpose $purpose, string $code): Otp
    {
        $otp = $this->otpRepository->findLatestActive($identifier, $channel, $purpose);

        if (! $otp) {
            throw new InvalidOtpException();
        }

        $maxAttempts = (int) config('auth-package.otp.max_attempts', 5);

        if ($otp->attempts >= $maxAttempts) {
            throw new InvalidOtpException('Too many attempts. Request a new code.');
        }

        if (! Hash::check($code, $otp->code)) {
            $this->otpRepository->incrementAttempts($otp);

            throw new InvalidOtpException();
        }

        $this->otpRepository->markAsUsed($otp);

        return $otp;
    }

    protected function dispatch(Otp $otp, ?User $user, string $plainCode): void
    {
        if ($otp->channel === OtpChannel::Email) {
            $this->sendEmailOtp($otp, $user, $plainCode);

            return;
        }

        $this->smsSender->sendOtp($otp->identifier, $plainCode, $otp->purpose, $user);
    }

    protected function sendEmailOtp(Otp $otp, ?User $user, string $plainCode): void
    {
        if ($user) {
            $user->notify(new OtpNotification($otp, $plainCode));

            return;
        }

        Mail::to($otp->identifier)->send(new OtpCodeMail($otp, $plainCode));
    }

    protected function ensureNotThrottled(string $identifier, OtpChannel $channel, OtpPurpose $purpose): void
    {
        $key = $this->throttleKey($identifier, $channel, $purpose);
        $maxAttempts = (int) config('auth-package.otp.throttle_max_attempts', 1);
        $decaySeconds = (int) config('auth-package.otp.throttle_seconds', 60);

        if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            throw new TooManyOtpAttemptsException(RateLimiter::availableIn($key));
        }

        RateLimiter::hit($key, $decaySeconds);
    }

    protected function throttleKey(string $identifier, OtpChannel $channel, OtpPurpose $purpose): string
    {
        return 'auth-otp:'.sha1($identifier.'|'.$channel->value.'|'.$purpose->value);
    }
}
