<?php

declare(strict_types=1);

namespace AhmedAshraf\Auth\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class ResetPasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'token' => ['required_without:otp_code', 'string'],
            'email' => ['required_with:token', 'email'],
            'phone' => ['required_with:otp_code', 'string'],
            'otp_code' => ['required_without:token', 'string', 'size:'.config('auth-package.otp.length', 6)],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ];
    }
}
