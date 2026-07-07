<?php

declare(strict_types=1);

namespace Ashtech\LaravelAuthKit\Services;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Ashtech\LaravelAuthKit\Contracts\OtpRepositoryInterface;
use Ashtech\LaravelAuthKit\Contracts\SmsSenderInterface;
use Ashtech\LaravelAuthKit\Enums\OtpChannel;
use Ashtech\LaravelAuthKit\Enums\OtpPurpose;
use Ashtech\LaravelAuthKit\Exceptions\InvalidOtpException;
use Ashtech\LaravelAuthKit\Exceptions\TooManyOtpAttemptsException;
use Ashtech\LaravelAuthKit\Mail\OtpCodeMail;
use Ashtech\LaravelAuthKit\Models\Otp;
use Ashtech\LaravelAuthKit\Models\User;
use Ashtech\LaravelAuthKit\Notifications\OtpNotification;
use Ashtech\LaravelAuthKit\Support\OtpSendValidator;

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

        $length = (int) config('laravel-auth-kit.otp.length', 6);
        $plainCode = str_pad((string) random_int(0, (10 ** $length) - 1), $length, '0', STR_PAD_LEFT);

        $otp = $this->otpRepository->create([
            'user_id' => $user?->id,
            'identifier' => $identifier,
            'channel' => $channel,
            'code' => Hash::make($plainCode),
            'purpose' => $purpose,
            'expires_at' => now()->addMinutes((int) config('laravel-auth-kit.otp.expires_minutes', 10)),
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

        $maxAttempts = (int) config('laravel-auth-kit.otp.max_attempts', 5);

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
        $maxAttempts = (int) config('laravel-auth-kit.otp.throttle_max_attempts', 1);
        $decaySeconds = (int) config('laravel-auth-kit.otp.throttle_seconds', 60);

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
