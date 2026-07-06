<?php

declare(strict_types=1);

namespace AhmedAshraf\Auth\Notifications;

use Illuminate\Notifications\Notification;
use AhmedAshraf\Auth\Mail\OtpCodeMail;
use AhmedAshraf\Auth\Models\Otp;

class OtpNotification extends Notification
{
    public function __construct(
        public Otp $otp,
        public string $plainCode,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): OtpCodeMail
    {
        return (new OtpCodeMail($this->otp, $this->plainCode, $notifiable->name ?? null))
            ->to($notifiable->email);
    }
}
