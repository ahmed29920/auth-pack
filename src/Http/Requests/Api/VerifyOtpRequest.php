<?php

declare(strict_types=1);

namespace AhmedAshraf\Auth\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;
use AhmedAshraf\Auth\Http\Requests\Concerns\ValidatesGuestOtpInput;

class VerifyOtpRequest extends FormRequest
{
    use ValidatesGuestOtpInput;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return array_merge($this->guestOtpIdentifierRules(), [
            'code' => ['required', 'string', 'size:'.config('auth-package.otp.length', 6)],
        ]);
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $this->validateGuestOtpChannel($validator);
        });
    }
}
