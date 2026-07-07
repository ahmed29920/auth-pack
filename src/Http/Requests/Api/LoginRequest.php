<?php

declare(strict_types=1);

namespace Ashtech\LaravelAuthKit\Http\Requests\Api;

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
                'otp_code' => ['required', 'string', 'size:'.config('laravel-auth-kit.otp.length', 6)],
            ];

            if (config('laravel-auth-kit.methods.phone_otp')) {
                $rules['phone'] = ['required_without:email', 'nullable', 'string'];
            }

            if (config('laravel-auth-kit.methods.email_otp')) {
                $rules['email'] = ['required_without:phone', 'nullable', 'email'];
            }

            return $rules;
        }

        $rules = [
            'password' => ['required', 'string'],
        ];

        if (config('laravel-auth-kit.methods.email_password')) {
            $rules['email'] = ['required_without:phone', 'nullable', 'email'];
        }

        if (config('laravel-auth-kit.methods.phone_password')) {
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

        return ! config('laravel-auth-kit.methods.email_password')
            && ! config('laravel-auth-kit.methods.phone_password')
            && (config('laravel-auth-kit.methods.email_otp') || config('laravel-auth-kit.methods.phone_otp'));
    }
}
