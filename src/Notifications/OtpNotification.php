<?php

declare(strict_types=1);

namespace Ashtech\LaravelAuthKit\Notifications;

use Illuminate\Notifications\Notification;
use Ashtech\LaravelAuthKit\Mail\OtpCodeMail;
use Ashtech\LaravelAuthKit\Models\Otp;

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
