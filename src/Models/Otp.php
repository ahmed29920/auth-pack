<?php

declare(strict_types=1);

namespace AhmedAshraf\Auth\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use AhmedAshraf\Auth\Enums\OtpChannel;
use AhmedAshraf\Auth\Enums\OtpPurpose;

class Otp extends Model
{
    protected $fillable = [
        'user_id',
        'identifier',
        'channel',
        'code',
        'purpose',
        'is_used',
        'attempts',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'channel' => OtpChannel::class,
            'purpose' => OtpPurpose::class,
            'is_used' => 'boolean',
            'expires_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(config('auth-package.user_model'));
    }

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    public function isValid(): bool
    {
        return ! $this->is_used && ! $this->isExpired();
    }
}
