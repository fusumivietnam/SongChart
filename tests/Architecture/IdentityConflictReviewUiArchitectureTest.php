<?php

declare(strict_types=1);

use App\Support\DomainContracts\UseCaseContractRegistry;

it('keeps identity conflict decisions contract-governed and capability protected', function (): void {
    $composer = json_decode((string) file_get_contents(base_path('composer.json')), true, flags: JSON_THROW_ON_ERROR);
    $contract = app(UseCaseContractRegistry::class)->get('admin.identity-conflicts.decide');

    expect($composer['scripts']['identity-conflict-ui:verify'] ?? null)->toBe('@php scripts/verify-identity-conflict-review-ui.php')
        ->and($composer['scripts']['quality:verify'] ?? [])->toContain('@identity-conflict-ui:verify')
        ->and($contract->readOnly)->toBeFalse()
        ->and($contract->middleware)->toContain('can:manage-identity-conflicts', 'password.confirm')
        ->and(collect($contract->supportSurfaces)->flatMap(fn ($surface) => $surface->writeFields)->all() !== [])->toBeTrue();
});

it('keeps the controller behind the audited identity review service', function (): void {
    $source = (string) file_get_contents(app_path('Http/Controllers/Admin/IdentityConflictReviewController.php'));

    expect($source)->toContain('IdentityConflictReviewService')
        ->and(str_contains($source, 'DB::'))->toBeFalse()
        ->and(str_contains($source, '->update('))->toBeFalse()
        ->and(str_contains($source, '->create('))->toBeFalse();
});

it('resolves identity conflict mutation through the shared authorization capability authority', function (): void {
    $routes = (string) file_get_contents(base_path('routes/web.php'));
    $provider = (string) file_get_contents(app_path('Providers/AuthorizationServiceProvider.php'));
    $capabilities = (string) file_get_contents(app_path('Enums/Capability.php'));
    $authorization = json_decode(
        (string) file_get_contents(base_path('docs/project/security/authorization-contract.json')),
        true,
        512,
        JSON_THROW_ON_ERROR,
    );

    expect(str_contains($routes, 'can:manage-identity-conflicts'))->toBeTrue()
        ->and(str_contains($capabilities, "case ManageIdentityConflicts = 'manage-identity-conflicts';"))->toBeTrue()
        ->and(str_contains($provider, 'foreach (Capability::cases() as $capability)'))->toBeTrue()
        ->and(in_array('manage-identity-conflicts', $authorization['roles']['reviewer'] ?? [], true))->toBeTrue()
        ->and(in_array('manage-identity-conflicts', $authorization['roles']['super_admin'] ?? [], true))->toBeTrue()
        ->and(in_array('manage-identity-conflicts', $authorization['roles']['system_operator'] ?? [], true))->toBeFalse();
});
