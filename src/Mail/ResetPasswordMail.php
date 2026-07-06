<?php

declare(strict_types=1);

namespace AhmedAshraf\Auth\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ResetPasswordMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public string $resetUrl,
        public ?string $recipientName = null,
        public int $expireMinutes = 60,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('kango-auth::auth.mail.reset_password_subject', ['app' => config('app.name')]),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'kango-auth::mail.reset-password',
            with: [
                'resetUrl' => $this->resetUrl,
                'recipientName' => $this->recipientName,
                'expireMinutes' => $this->expireMinutes,
                'appName' => config('app.name'),
            ],
        );
    }
}
