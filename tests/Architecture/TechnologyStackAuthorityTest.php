<?php

declare(strict_types=1);

it('keeps technology stack verification in the quality chain', function (): void {
    $composer = json_decode((string) file_get_contents(base_path('composer.json')), true, 512, JSON_THROW_ON_ERROR);

    expect($composer['scripts']['stack:verify'] ?? null)
        ->toBe('@php scripts/verify-stack-baseline.php')
        ->and($composer['scripts']['quality:verify'] ?? [])
        ->toContain('@stack:verify');
});

it('publishes the stack authorities and machine manifest', function (): void {
    $directory = base_path('docs/project/stack');

    expect($directory.'/STACK_OVERVIEW.md')->toBeFile()
        ->and($directory.'/FRAMEWORK_BASELINE.md')->toBeFile()
        ->and($directory.'/PACKAGE_REGISTRY.md')->toBeFile()
        ->and($directory.'/CAPABILITY_OWNERSHIP.md')->toBeFile()
        ->and($directory.'/PACKAGE_ADOPTION_POLICY.md')->toBeFile()
        ->and($directory.'/DEPRECATION_REGISTRY.md')->toBeFile()
        ->and($directory.'/stack-manifest.json')->toBeFile();
});

it('keeps framework ownership and provider identity policies explicit', function (): void {
    $manifest = json_decode((string) file_get_contents(base_path('docs/project/stack/stack-manifest.json')), true, 512, JSON_THROW_ON_ERROR);

    expect($manifest['policies']['laravel_native_first'] ?? false)->toBeTrue()
        ->and($manifest['policies']['new_packages_require_review'] ?? false)->toBeTrue()
        ->and($manifest['policies']['provider_ids_may_be_primary_keys'] ?? true)->toBeFalse()
        ->and($manifest['capabilities']['authentication'] ?? null)->toBe('laravel/fortify')
        ->and($manifest['capabilities']['catalog_identity'] ?? null)->toBe('songchart-canonical-catalog');
});
