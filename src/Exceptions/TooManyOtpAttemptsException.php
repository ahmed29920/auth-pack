<?php

declare(strict_types=1);

namespace Ashtech\LaravelAuthKit\Exceptions;

use Exception;

class TooManyOtpAttemptsException extends Exception
{
    public function __construct(
        public readonly int $retryAfterSeconds = 60,
    ) {
        parent::__construct(__('laravel-auth-kit::auth.otp.throttled', ['seconds' => $retryAfterSeconds]));
    }
}
