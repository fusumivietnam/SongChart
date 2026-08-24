<?php

declare(strict_types=1);

use App\Enums\Capability;
use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;

uses(RefreshDatabase::class);

it('registers every capability as a Laravel Gate from the shared authorization matrix', function (): void {
    $superAdmin = User::factory()->create([
        'role' => UserRole::SuperAdmin,
        'is_active' => true,
    ]);

    foreach (Capability::cases() as $capability) {
        expect(Gate::forUser($superAdmin)->allows($capability->value))->toBeTrue();
    }
});

it('denies every capability for inactive users regardless of role', function (): void {
    $inactive = User::factory()->create([
        'role' => UserRole::SuperAdmin,
        'is_active' => false,
    ]);

    foreach (Capability::cases() as $capability) {
        expect(Gate::forUser($inactive)->allows($capability->value))->toBeFalse();
    }
});

it('keeps role and activation mutations on distinct capabilities', function (): void {
    $operator = User::factory()->create([
        'role' => UserRole::SystemOperator,
        'is_active' => true,
    ]);

    expect(Gate::forUser($operator)->allows(Capability::ManageUserActivation->value))->toBeTrue()
        ->and(Gate::forUser($operator)->allows(Capability::ManageUserRoles->value))->toBeFalse();
});
