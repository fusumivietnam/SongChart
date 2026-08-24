<?php

declare(strict_types=1);

it('keeps catalog verification in the quality chain', function (): void {
    $composer = json_decode((string) file_get_contents(base_path('composer.json')), true, 512, JSON_THROW_ON_ERROR);

    expect($composer['scripts']['catalog:verify'] ?? null)
        ->toBe('@php scripts/verify-catalog-model.php')
        ->and($composer['scripts']['quality:verify'] ?? [])
        ->toContain('@catalog:verify');
});

it('keeps provider identifiers outside canonical primary keys', function (): void {
    $migration = (string) file_get_contents(base_path('database/migrations/2026_08_04_000200_create_catalog_provenance_mapping_tables.php'));

    expect($migration)->toContain("\$table->ulid('entity_id')")
        ->and($migration)->not->toContain("\$table->string('external_id')->primary");
});

it('keeps Stage 12.1 identity invariants in migrations and models', function (): void {
    $migration = (string) file_get_contents(base_path('database/migrations/2026_08_04_000300_harden_catalog_identity_invariants.php'));
    $entityMatch = (string) file_get_contents(base_path('app/Models/Catalog/EntityMatch.php'));
    $metadataConflict = (string) file_get_contents(base_path('app/Models/Catalog/MetadataConflict.php'));

    expect($migration)->toContain('provider_active_match_unique')
        ->and($migration)->toContain('metadata_conflict_pair_unique')
        ->and($entityMatch)->toContain('active_match_key')
        ->and($metadataConflict)->toContain('normalized_pair_key');
});
