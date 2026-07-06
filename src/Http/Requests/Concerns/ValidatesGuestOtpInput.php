<?php

declare(strict_types=1);

namespace AhmedAshraf\Auth\Http\Requests\Concerns;

use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;
use AhmedAshraf\Auth\Enums\OtpChannel;
use AhmedAshraf\Auth\Enums\OtpPurpose;
use AhmedAshraf\Auth\Support\AuthMethods;
use AhmedAshraf\Auth\Support\OtpSendValidator;

trait ValidatesGuestOtpInput
{
    /**
     * @return list<string>
     */
    protected function guestOtpPurposeValues(): array
    {
        return array_map(
            fn (OtpPurpose $purpose) => $purpose->value,
            OtpSendValidator::guestSendPurposes(),
        );
    }

    /**
     * @return array<string, mixed>
     */
    protected function guestOtpIdentifierRules(): array
    {
        return [
            'email' => ['required_without:phone', 'nullable', 'email'],
            'phone' => ['required_without:email', 'nullable', 'string'],
            'purpose' => ['required', Rule::enum(OtpPurpose::class), Rule::in($this->guestOtpPurposeValues())],
        ];
    }

    protected function validateGuestOtpChannel(Validator $validator): void
    {
        if ($validator->errors()->isNotEmpty()) {
            return;
        }

        $channel = $this->filled('phone') ? OtpChannel::Phone : OtpChannel::Email;

        if (! OtpSendValidator::channelEnabled($channel)) {
            $validator->errors()->add(
                $channel === OtpChannel::Phone ? 'phone' : 'email',
                __('kango-auth::auth.otp.channel_disabled'),
            );
        }

        $purpose = OtpPurpose::from($this->input('purpose'));

        if ($purpose === OtpPurpose::ResetPassword && ! AuthMethods::allowsPhonePasswordReset()) {
            $validator->errors()->add('purpose', __('kango-auth::auth.otp.reset_not_allowed'));
        }
    }
}
