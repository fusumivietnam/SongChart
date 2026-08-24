<?php

declare(strict_types=1);

use App\Support\DomainContracts\DTO\EntityDataSurface;
use App\Support\DomainContracts\DTO\UseCaseContract;
use App\Support\DomainContracts\UseCaseContractRegistry;

it('keeps executable use-case and type guardrails in the quality boundary', function (): void {
    $composer = json_decode((string) file_get_contents(base_path('composer.json')), true, flags: JSON_THROW_ON_ERROR);

    expect(base_path('docs/project/domain/use-case-contracts.json'))->toBeFile()
        ->and(base_path('scripts/verify-use-case-contracts.php'))->toBeFile()
        ->and(base_path('scripts/verify-type-guardrails.php'))->toBeFile()
        ->and($composer['scripts']['use-case-contracts:verify'] ?? null)->toBe('@php scripts/verify-use-case-contracts.php')
        ->and($composer['scripts']['type-guardrails:verify'] ?? null)->toBe('@php scripts/verify-type-guardrails.php')
        ->and($composer['scripts']['quality:verify'] ?? [])->toContain('@use-case-contracts:verify')
        ->and($composer['scripts']['quality:verify'] ?? [])->toContain('@type-guardrails:verify');
});

it('hydrates typed use-case contracts without mixed runtime objects', function (): void {
    $contract = app(UseCaseContractRegistry::class)->get('admin.catalog.index');

    expect($contract)->toBeInstanceOf(UseCaseContract::class)
        ->and($contract->readOnly)->toBeTrue()
        ->and($contract->dataSurfaces)->not->toBeEmpty()
        ->and($contract->dataSurfaces[0])->toBeInstanceOf(EntityDataSurface::class)
        ->and($contract->dataSurfaces[0]->writeFields)->toBe([])
        ->and($contract->middleware)->toBe(['web', 'auth', 'active', 'verified', 'can:access-admin', 'two-factor.confirmed']);
});

it('prevents catalog administration from owning a duplicate display-field map', function (): void {
    $source = (string) file_get_contents(app_path('Support/Admin/CatalogAdministration.php'));

    expect($source)->toContain('DomainContractRegistry')
        ->and($source)->toContain('$this->contracts->displayField(')
        ->and($source)->not->toContain("'title' => 'name'")
        ->and($source)->not->toContain("'title' => 'title'");
});
