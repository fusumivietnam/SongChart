<?php

declare(strict_types=1);

it('keeps normalized provider data typed and independent from eloquent models', function (): void {
    $root = dirname(__DIR__, 2);
    $entity = file_get_contents($root.'/app/Domain/Providers/Catalog/DTO/NormalizedProviderEntity.php') ?: '';
    $field = file_get_contents($root.'/app/Domain/Providers/Normalization/ValueObjects/ProviderField.php') ?: '';

    expect($entity)->toContain('NormalizedEntityData $data')
        ->not->toContain('array $attributes')
        ->and($field)->toContain('FieldPresence')
        ->not->toContain('Illuminate\\Database\\Eloquent');
});
