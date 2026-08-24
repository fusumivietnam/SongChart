<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$required = [
    'database/migrations/2026_08_04_001000_create_provider_import_ledger_tables.php',
    'app/Models/Providers/Ingestion/ProviderImportRun.php',
    'app/Models/Providers/Ingestion/ProviderImportRequest.php',
    'app/Models/Providers/Ingestion/ProviderImportPayload.php',
    'app/Models/Providers/Ingestion/ProviderImportItem.php',
    'app/Models/Providers/Ingestion/ProviderImportFailure.php',
    'app/Models/Providers/Ingestion/ProviderImportCheckpoint.php',
    'app/Support/Providers/Ingestion/SensitiveDataRedactor.php',
    'tests/Feature/Providers/ProviderImportLedgerTest.php',
    'tests/Architecture/ProviderImportLedgerBoundaryTest.php',
    'docs/providers/RAW_INGESTION_LEDGER.md',
];
$errors = [];
foreach ($required as $path) {
    if (! is_file($root.DIRECTORY_SEPARATOR.$path)) {
        $errors[] = "Missing required file: {$path}";
    }
}
$migration = @file_get_contents($root.'/database/migrations/2026_08_04_001000_create_provider_import_ledger_tables.php') ?: '';
foreach (['provider_import_runs', 'provider_import_requests', 'provider_import_payloads', 'provider_import_items', 'provider_import_failures', 'provider_import_checkpoints', 'payload_hash', 'configuration_hash'] as $needle) {
    if (! str_contains($migration, $needle)) {
        $errors[] = "Ledger migration must contain {$needle}.";
    }
}
$payload = @file_get_contents($root.'/app/Models/Providers/Ingestion/ProviderImportPayload.php') ?: '';
if (! str_contains($payload, 'Raw provider payloads are immutable.')) {
    $errors[] = 'Raw provider payload immutability guard is missing.';
}
if ($errors !== []) {
    fwrite(STDERR, "Provider import ledger verification failed:\n- ".implode("\n- ", $errors)."\n");
    exit(1);
}
echo "Provider import ledger verification passed.\n";
