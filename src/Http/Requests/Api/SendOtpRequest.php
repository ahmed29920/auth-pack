<?php

declare(strict_types=1);

namespace Ashtech\LaravelAuthKit\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;
use Ashtech\LaravelAuthKit\Http\Requests\Concerns\ValidatesGuestOtpInput;

class SendOtpRequest extends FormRequest
{
    use ValidatesGuestOtpInput;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return $this->guestOtpIdentifierRules();
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $this->validateGuestOtpChannel($validator);
        });
    }
}
