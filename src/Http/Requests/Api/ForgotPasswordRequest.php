<?php

declare(strict_types=1);

namespace Ashtech\LaravelAuthKit\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Ashtech\LaravelAuthKit\Support\AuthMethods;

class ForgotPasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [];

        if (AuthMethods::allowsEmailPasswordReset()) {
            $rules['email'] = [
                AuthMethods::allowsPhonePasswordReset() ? 'required_without:phone' : 'required',
                'nullable',
                'email',
            ];
        }

        if (AuthMethods::allowsPhonePasswordReset()) {
            $rules['phone'] = [
                AuthMethods::allowsEmailPasswordReset() ? 'required_without:email' : 'required',
                'nullable',
                'string',
            ];
        }

        return $rules;
    }
}
