<?php

declare(strict_types=1);

use App\Enums\Capability;
use App\Enums\UserRole;
use App\Support\Auth\AuthorizationMatrix;

it('resolves the static SongChart role capability matrix from the machine authority', function (): void {
    $root = dirname(__DIR__, 2);
    $matrix = new AuthorizationMatrix($root.'/docs/project/security/authorization-contract.json');

    expect($matrix->roleAllows(UserRole::User, Capability::AccessAdmin))->toBeFalse()
        ->and($matrix->roleAllows(UserRole::Reviewer, Capability::ManageIdentityConflicts))->toBeTrue()
        ->and($matrix->roleAllows(UserRole::ProviderManager, Capability::ManageProviders))->toBeTrue()
        ->and($matrix->roleAllows(UserRole::SystemOperator, Capability::ManageUserActivation))->toBeTrue()
        ->and($matrix->roleAllows(UserRole::SystemOperator, Capability::ManageUserRoles))->toBeFalse();

    foreach (Capability::cases() as $capability) {
        expect($matrix->roleAllows(UserRole::SuperAdmin, $capability))->toBeTrue();
    }
});
