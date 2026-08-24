<?php

declare(strict_types=1);

it('keeps raw ingestion persistence separate from canonical models', function (): void {
    $migration = file_get_contents(base_path('database/migrations/2026_08_04_001000_create_provider_import_ledger_tables.php'));
    $payload = file_get_contents(app_path('Models/Providers/Ingestion/ProviderImportPayload.php'));

    expect($migration)->toContain("Schema::create('provider_import_runs'")
        ->toContain("Schema::create('provider_import_payloads'")
        ->toContain('payload_hash')
        ->and($payload)->toContain('Raw provider payloads are immutable.')
        ->not->toContain('App\\Models\\Catalog');
});
