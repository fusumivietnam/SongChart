<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\UserRole;
use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;

final class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasFactory;

    use HasUlids;
    use Notifiable;
    use TwoFactorAuthenticatable;

    protected $fillable = ['name', 'email', 'password'];

    protected $hidden = ['password', 'remember_token', 'two_factor_recovery_codes', 'two_factor_secret'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'role' => UserRole::class,
            'two_factor_confirmed_at' => 'datetime',
        ];
    }

    public function hasTwoFactorAuthenticationConfigured(): bool
    {
        return ($this->getAttributes()['two_factor_secret'] ?? null) !== null;
    }

    public function hasPendingTwoFactorAuthentication(): bool
    {
        $attributes = $this->getAttributes();

        return ($attributes['two_factor_secret'] ?? null) !== null
            && ($attributes['two_factor_confirmed_at'] ?? null) === null;
    }

    public function hasConfirmedTwoFactorAuthentication(): bool
    {
        $attributes = $this->getAttributes();

        return ($attributes['two_factor_secret'] ?? null) !== null
            && ($attributes['two_factor_confirmed_at'] ?? null) !== null;
    }

    public function resolvedAdminRole(): ?UserRole
    {
        $role = $this->getAttributes()['role'] ?? null;

        return is_string($role) ? UserRole::tryFrom($role) : null;
    }
}
