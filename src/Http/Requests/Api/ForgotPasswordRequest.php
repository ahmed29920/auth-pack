<?php

declare(strict_types=1);

namespace AhmedAshraf\Auth\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use AhmedAshraf\Auth\Support\AuthMethods;

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
