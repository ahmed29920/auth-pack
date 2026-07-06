<?php

declare(strict_types=1);

namespace AhmedAshraf\Auth\Exceptions;

use Exception;

class VerificationRequiredException extends Exception
{
    public function __construct(
        public readonly array $pending = [],
    ) {
        parent::__construct('Account verification is required before you can continue.');
    }
}
