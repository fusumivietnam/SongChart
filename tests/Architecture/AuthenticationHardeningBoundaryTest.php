<?php

declare(strict_types=1);

use App\Models\User;

it('keeps privileged authentication safeguards in the Laravel middleware boundary', function (): void {
    $routes = file_get_contents(base_path('routes/web.php'));
    $bootstrap = file_get_contents(base_path('bootstrap/app.php'));
    $seeder = file_get_contents(database_path('seeders/DatabaseSeeder.php'));

    expect($routes)
        ->toContain("'active'")
        ->toContain("'two-factor.confirmed'")
        ->toContain("'password.confirm'")
        ->and($bootstrap)
        ->toContain('EnsureUserIsActive::class')
        ->toContain('EnsureConfirmedTwoFactorAuthentication::class')
        ->and(str_contains((string) $seeder, 'ChangeMe123!'))->toBeFalse()
        ->and(str_contains((string) $seeder, 'admin@songchart.test'))->toBeFalse()
        ->and((new User)->getFillable())
        ->toBe(['name', 'email', 'password']);
});

it('keeps authorization capability ownership out of User and UserRole', function (): void {
    $user = (string) file_get_contents(app_path('Models/User.php'));
    $role = (string) file_get_contents(app_path('Enums/UserRole.php'));
    $provider = (string) file_get_contents(app_path('Providers/AuthorizationServiceProvider.php'));

    foreach (['canAccessAdmin', 'canManageExtensions', 'canManageProviders', 'canReviewIdentityConflicts', 'canViewOperations'] as $legacyMethod) {
        expect(str_contains($user, $legacyMethod))->toBeFalse()
            ->and(str_contains($role, $legacyMethod))->toBeFalse();
    }

    expect(str_contains($provider, 'foreach (Capability::cases() as $capability)'))->toBeTrue()
        ->and(str_contains($provider, 'AuthorizationMatrix::class'))->toBeTrue();
});
