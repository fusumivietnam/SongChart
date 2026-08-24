<?php

declare(strict_types=1);

use App\Domain\Catalog\Enums\EntityType;
use App\Support\DomainContracts\DomainContractRegistry;

it('keeps the domain contract registry in the executable quality boundary', function (): void {
    $composer = json_decode((string) file_get_contents(base_path('composer.json')), true, flags: JSON_THROW_ON_ERROR);

    expect(base_path('docs/project/domain/domain-contracts.json'))->toBeFile()
        ->and(base_path('scripts/verify-domain-contracts.php'))->toBeFile()
        ->and($composer['scripts']['domain-contracts:verify'] ?? null)->toBe('@php scripts/verify-domain-contracts.php')
        ->and($composer['scripts']['quality:verify'] ?? [])->toContain('@domain-contracts:verify');
});

it('resolves schema semantics from the registry instead of entity-name guesses', function (): void {
    $registry = app(DomainContractRegistry::class);

    expect($registry->displayField(EntityType::Artist))->toBe('name')
        ->and($registry->displayField(EntityType::Version))->toBe('name')
        ->and($registry->displayField(EntityType::Recording))->toBe('title')
        ->and($registry->dateField(EntityType::Recording))->toBeNull()
        ->and($registry->dateField(EntityType::ReleaseGroup))->toBe('first_release_date')
        ->and($registry->dateField(EntityType::Release))->toBe('released_on')
        ->and($registry->descriptionField(EntityType::Collection))->toBe('description');
});
