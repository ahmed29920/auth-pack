<?php

declare(strict_types=1);

namespace AhmedAshraf\Auth\Exceptions;

use Exception;

class TooManyOtpAttemptsException extends Exception
{
    public function __construct(
        public readonly int $retryAfterSeconds = 60,
    ) {
        parent::__construct(__('kango-auth::auth.otp.throttled', ['seconds' => $retryAfterSeconds]));
    }
}
