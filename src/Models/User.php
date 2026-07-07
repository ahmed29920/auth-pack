<?php

declare(strict_types=1);

namespace Ashtech\LaravelAuthKit\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Ashtech\LaravelAuthKit\Enums\UserRole;
use Ashtech\LaravelAuthKit\Support\VerificationRequirements;
use Ashtech\LaravelAuthKit\Traits\ManagesAuthRoles;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens;
    use HasFactory;
    use ManagesAuthRoles;
    use Notifiable;
    use SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'role',
        'vendor_id',
        'email_verified_at',
        'phone_verified_at',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'phone_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'role' => UserRole::class,
        ];
    }

    public function isVerifiedEmail(): bool
    {
        return $this->email_verified_at !== null;
    }

    public function isVerifiedPhone(): bool
    {
        return $this->phone_verified_at !== null;
    }

    public function needsEmailVerification(): bool
    {
        return VerificationRequirements::userNeedsEmailVerification($this);
    }

    public function needsPhoneVerification(): bool
    {
        return VerificationRequirements::userNeedsPhoneVerification($this);
    }

    public function needsVerification(): bool
    {
        return VerificationRequirements::userNeedsVerification($this);
    }

    /**
     * @return list<string>
     */
    public function pendingVerificationChannels(): array
    {
        return VerificationRequirements::pendingChannels($this);
    }
}
