<?php

declare(strict_types=1);

namespace Ashtech\LaravelAuthKit\Exceptions;

use Exception;

class InvalidOtpException extends Exception
{
    public function __construct(string $message = 'Invalid or expired OTP.')
    {
        parent::__construct($message);
    }
}
