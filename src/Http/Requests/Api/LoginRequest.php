<?php

declare(strict_types=1);

namespace AhmedAshraf\Auth\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        if ($this->isOtpLogin()) {
            $rules = [
                'otp_code' => ['required', 'string', 'size:'.config('auth-package.otp.length', 6)],
            ];

            if (config('auth-package.methods.phone_otp')) {
                $rules['phone'] = ['required_without:email', 'nullable', 'string'];
            }

            if (config('auth-package.methods.email_otp')) {
                $rules['email'] = ['required_without:phone', 'nullable', 'email'];
            }

            return $rules;
        }

        $rules = [
            'password' => ['required', 'string'],
        ];

        if (config('auth-package.methods.email_password')) {
            $rules['email'] = ['required_without:phone', 'nullable', 'email'];
        }

        if (config('auth-package.methods.phone_password')) {
            $rules['phone'] = ['required_without:email', 'nullable', 'string'];
        }

        return $rules;
    }

    protected function isOtpLogin(): bool
    {
        if ($this->filled('otp_code')) {
            return true;
        }

        if ($this->input('login_mode') === 'otp') {
            return true;
        }

        return ! config('auth-package.methods.email_password')
            && ! config('auth-package.methods.phone_password')
            && (config('auth-package.methods.email_otp') || config('auth-package.methods.phone_otp'));
    }
}
