<?php

declare(strict_types=1);

namespace Ashtech\LaravelAuthKit\Exceptions;

use Exception;

class AccountInactiveException extends Exception
{
    public function __construct(?string $message = null)
    {
        parent::__construct($message ?? __('laravel-auth-kit::auth.account.inactive'));
    }
}
