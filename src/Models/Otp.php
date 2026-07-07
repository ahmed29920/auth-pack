<?php

declare(strict_types=1);

namespace Ashtech\LaravelAuthKit\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Ashtech\LaravelAuthKit\Enums\OtpChannel;
use Ashtech\LaravelAuthKit\Enums\OtpPurpose;

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
        return $this->belongsTo(config('laravel-auth-kit.user_model'));
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
