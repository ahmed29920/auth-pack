<?php

declare(strict_types=1);

namespace Ashtech\LaravelAuthKit\Enums;

enum OtpChannel: string
{
    case Email = 'email';
    case Phone = 'phone';
}
