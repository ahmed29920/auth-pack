<?php

declare(strict_types=1);

namespace Ashtech\LaravelAuthKit\Exceptions;

use Exception;

class AuthenticationException extends Exception
{
    public function __construct(string $message = 'Invalid credentials.')
    {
        parent::__construct($message);
    }
}
