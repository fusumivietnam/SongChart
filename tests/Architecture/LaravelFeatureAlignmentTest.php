<?php

declare(strict_types=1);

it('keeps the executable Laravel alignment guardrail in the verification chain', function (): void {
    $composer = json_decode((string) file_get_contents(base_path('composer.json')), true, flags: JSON_THROW_ON_ERROR);

    expect($composer['scripts']['alignment:verify'] ?? null)
        ->toBe('@php scripts/verify-laravel-alignment.php');

    $topology = json_decode((string) file_get_contents(base_path('docs/project/engineering/verification-topology.json')), true, 512, JSON_THROW_ON_ERROR);

    expect($composer['scripts']['quality:verify'] ?? [])
        ->toContain('@alignment:verify')
        ->and(array_key_exists('verify', $composer['scripts']))->toBeFalse()
        ->and($composer['scripts']['stage:verify'] ?? null)
        ->toBe($topology['lanes']['stage']['ordered_steps']);
});

it('documents every feature alignment classification', function (): void {
    $audit = (string) file_get_contents(base_path('docs/foundation/STAGE_11_3_LARAVEL_FEATURE_ALIGNMENT_AUDIT.md'));

    expect($audit)
        ->toContain('Aligned')
        ->toContain('Accepted custom')
        ->toContain('Deferred')
        ->toContain('Remediation candidate');
});

it('keeps public search and extension writes behind request and action boundaries', function (): void {
    $search = (string) file_get_contents(base_path('app/Http/Controllers/Search/SearchController.php'));
    $extensions = (string) file_get_contents(base_path('app/Http/Controllers/Admin/ExtensionController.php'));

    expect($search)
        ->toContain('SearchRequest')
        ->toContain('BuildSearchPage')
        ->and(str_contains($search, '->validate('))->toBeFalse()
        ->and(str_contains($search, 'Illuminate\\Http\\Request'))->toBeFalse();

    expect($extensions)
        ->toContain('Actions\\Admin\\Extensions')
        ->and(str_contains($extensions, 'App\\Extensions\\ExtensionManager'))->toBeFalse()
        ->and(str_contains($extensions, 'App\\Extensions\\ExtensionInstaller'))->toBeFalse()
        ->and(str_contains($extensions, 'App\\Extensions\\ExtensionUpgradeManager'))->toBeFalse()
        ->and(str_contains($extensions, 'App\\Extensions\\ExtensionPreflight'))->toBeFalse();
});

it('resolves admin authorization through the shared capability authority', function (): void {
    $routes = (string) file_get_contents(base_path('routes/web.php'));
    $provider = (string) file_get_contents(app_path('Providers/AuthorizationServiceProvider.php'));
    $capabilities = (string) file_get_contents(app_path('Enums/Capability.php'));
    $contract = json_decode(
        (string) file_get_contents(base_path('docs/project/security/authorization-contract.json')),
        true,
        512,
        JSON_THROW_ON_ERROR,
    );

    expect(str_contains($routes, 'can:access-admin'))->toBeTrue()
        ->and(str_contains($capabilities, "case AccessAdmin = 'access-admin';"))->toBeTrue()
        ->and(str_contains($provider, 'foreach (Capability::cases() as $capability)'))->toBeTrue()
        ->and(str_contains($provider, 'AuthorizationMatrix::class'))->toBeTrue()
        ->and(in_array('access-admin', $contract['roles']['super_admin'] ?? [], true))->toBeTrue();
});
