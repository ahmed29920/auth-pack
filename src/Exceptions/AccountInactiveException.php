<?php

declare(strict_types=1);

namespace AhmedAshraf\Auth\Exceptions;

use Exception;

class AccountInactiveException extends Exception
{
    public function __construct(?string $message = null)
    {
        parent::__construct($message ?? __('kango-auth::auth.account.inactive'));
    }
}
