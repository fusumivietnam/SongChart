<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/** @extends Factory<User> */
final class UserFactory extends Factory
{
    protected static ?string $password;

    public function unverified(): static
    {
        return $this->state(fn (array $attributes): array => ['email_verified_at' => null]);
    }

    public function withConfirmedTwoFactorAuthentication(): static
    {
        return $this->state(fn (array $attributes): array => [
            'two_factor_secret' => 'test-encrypted-secret',
            'two_factor_confirmed_at' => now(),
        ]);
    }

    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'role' => 'user',
            'is_active' => true,
            'remember_token' => Str::random(10),
        ];
    }
}
