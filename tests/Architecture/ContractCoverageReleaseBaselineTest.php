<?php

declare(strict_types=1);

use App\Support\DomainContracts\DTO\SupportDataSurface;
use App\Support\DomainContracts\UseCaseContractRegistry;

it('keeps operational contracts and impact mapping in the quality boundary', function (): void {
    $composer = json_decode((string) file_get_contents(base_path('composer.json')), true, flags: JSON_THROW_ON_ERROR);

    expect(base_path('docs/project/domain/operational-contracts.json'))->toBeFile()
        ->and(base_path('scripts/verify-operational-contracts.php'))->toBeFile()
        ->and(base_path('scripts/verify-impact-test-map.php'))->toBeFile()
        ->and($composer['scripts']['quality:verify'] ?? [])->toContain('@operational-contracts:verify')
        ->and($composer['scripts']['quality:verify'] ?? [])->toContain('@impact-map:verify');
});

it('hydrates route middleware and support-surface contracts', function (): void {
    $contract = app(UseCaseContractRegistry::class)->get('admin.catalog.show');

    expect($contract->method)->toBe('GET')
        ->and($contract->uri)->toBe('/admin/catalog/{type}/{id}')
        ->and($contract->middleware)->toBe(['web', 'auth', 'active', 'verified', 'can:access-admin', 'two-factor.confirmed'])
        ->and($contract->implementation)->toBe('app/Http/Controllers/Admin/CatalogController.php')
        ->and($contract->supportSurfaces !== [])->toBeTrue()
        ->and($contract->supportSurfaces[0])->toBeInstanceOf(SupportDataSurface::class);
});

it('keeps release delivery fail closed around dependency lockfiles', function (): void {
    $sourceVerifier = (string) file_get_contents(base_path('scripts/verify-source-package.php'));
    $packager = (string) file_get_contents(base_path('scripts/package-verified-source.php'));
    $provenanceVerifier = (string) file_get_contents(base_path('scripts/verify-artifact-provenance.php'));

    expect($sourceVerifier)->toContain("'composer.lock'")
        ->and($sourceVerifier)->toContain("'package-lock.json'")
        ->and($packager)->toContain('verify-artifact-provenance.php')
        ->and($provenanceVerifier)->toContain('closure_ready')
        ->and($provenanceVerifier)->toContain('source_tree_sha256')
        ->and($provenanceVerifier)->toContain('composer_lock_sha256')
        ->and($provenanceVerifier)->toContain('package_lock_sha256');
});

it('keeps roadmap future-looking and change-surface parsing bounded', function (): void {
    $roadmap = (string) file_get_contents(base_path('docs/project/docs/ROADMAP.md'));
    $changeSurface = (string) file_get_contents(base_path('scripts/verify-change-surface.php'));

    expect(str_contains($roadmap, '## Current stage'))->toBeFalse()
        ->and($changeSurface)->toContain("\$section('## Expected files')")
        ->and($changeSurface)->toContain('fnmatch(');
});
