<?php

declare(strict_types=1);

namespace Ashtech\LaravelAuthKit\Enums;

enum OtpPurpose: string
{
    case Login = 'login';
    case Register = 'register';
    case ResetPassword = 'reset_password';
    case VerifyEmail = 'verify_email';
    case VerifyPhone = 'verify_phone';
}
