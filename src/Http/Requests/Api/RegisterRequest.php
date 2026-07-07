<?php

declare(strict_types=1);

namespace Ashtech\LaravelAuthKit\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'role' => ['sometimes', Rule::in(config('laravel-auth-kit.registration_allowed_roles', ['customer']))],
            'vendor_id' => ['nullable', 'integer'],
        ];

        if (config('laravel-auth-kit.methods.email_password') || config('laravel-auth-kit.methods.email_otp')) {
            $rules['email'] = ['required_without:phone', 'nullable', 'email', 'max:255', 'unique:users,email'];
        }

        if (config('laravel-auth-kit.methods.phone_password') || config('laravel-auth-kit.methods.phone_otp')) {
            $rules['phone'] = ['required_without:email', 'nullable', 'string', 'max:30', 'unique:users,phone'];
        }

        return $rules;
    }
}
