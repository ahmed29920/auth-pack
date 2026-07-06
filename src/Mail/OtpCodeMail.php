<?php

declare(strict_types=1);

namespace AhmedAshraf\Auth\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use AhmedAshraf\Auth\Models\Otp;

class OtpCodeMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public Otp $otp,
        public string $plainCode,
        public ?string $recipientName = null,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('kango-auth::auth.mail.otp_subjects.'.$this->otp->purpose->value),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'kango-auth::mail.otp-code',
            with: [
                'code' => $this->plainCode,
                'purposeLabel' => __('kango-auth::auth.mail.otp_purposes.'.$this->otp->purpose->value),
                'expiresAt' => $this->otp->expires_at,
                'recipientName' => $this->recipientName,
                'appName' => config('app.name'),
            ],
        );
    }
}
