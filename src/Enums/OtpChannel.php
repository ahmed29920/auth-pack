<?php

declare(strict_types=1);

namespace AhmedAshraf\Auth\Enums;

enum OtpChannel: string
{
    case Email = 'email';
    case Phone = 'phone';
}
