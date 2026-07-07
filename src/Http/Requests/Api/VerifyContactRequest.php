<?php

declare(strict_types=1);

namespace Ashtech\LaravelAuthKit\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class VerifyContactRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'size:'.config('laravel-auth-kit.otp.length', 6)],
        ];
    }
}
