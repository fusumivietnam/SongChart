<?php

declare(strict_types=1);

namespace App\Providers;

use App\Enums\Capability;
use App\Models\User;
use App\Support\Auth\AuthorizationMatrix;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

final class AuthorizationServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(
            AuthorizationMatrix::class,
            static fn (): AuthorizationMatrix => new AuthorizationMatrix(
                base_path('docs/project/security/authorization-contract.json'),
            ),
        );
    }

    public function boot(): void
    {
        $matrix = $this->app->make(AuthorizationMatrix::class);

        foreach (Capability::cases() as $capability) {
            Gate::define(
                $capability->value,
                static fn (User $user): bool => $matrix->allows($user, $capability),
            );
        }
    }
}
