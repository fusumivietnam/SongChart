<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$required = [
    'app/Support/Providers/Ingestion/ProviderImportOrchestrator.php',
    'app/Jobs/Providers/Ingestion/FetchProviderImportPage.php',
    'app/Jobs/Providers/Ingestion/ProcessProviderImportPayload.php',
    'app/Jobs/Providers/Ingestion/FinalizeProviderImport.php',
    'database/migrations/2026_08_04_001100_add_provider_import_orchestration_fields.php',
    'tests/Feature/Providers/ProviderImportOrchestrationTest.php',
    'tests/Architecture/ProviderImportOrchestrationBoundaryTest.php',
    'docs/providers/PROVIDER_IMPORT_ORCHESTRATION.md',
    'docs/foundation/STAGE_13_3_TASK_CONTRACT.md',
];
$errors = [];
foreach ($required as $file) {
    if (! is_file($root.DIRECTORY_SEPARATOR.$file)) {
        $errors[] = "Missing {$file}";
    }
}
$composer = json_decode((string) file_get_contents($root.'/composer.json'), true, 512, JSON_THROW_ON_ERROR);
if (($composer['scripts']['provider:orchestration:verify'] ?? null) !== '@php scripts/verify-provider-import-orchestration.php') {
    $errors[] = 'provider:orchestration:verify is not registered.';
}
$quality = $composer['scripts']['quality:verify'] ?? [];
if (! in_array('@provider:orchestration:verify', $quality, true)) {
    $errors[] = 'Provider orchestration verifier is outside quality:verify.';
}
$status = (string) @file_get_contents($root.'/app/Domain/Providers/Ingestion/Enums/ProviderImportRunStatus.php');
foreach (['isTerminal', 'canTransitionTo', 'assertCanTransitionTo'] as $method) {
    if (! str_contains($status, $method)) {
        $errors[] = "Run status state machine is missing {$method}.";
    }
}
$fetch = (string) @file_get_contents($root.'/app/Jobs/Providers/Ingestion/FetchProviderImportPage.php');
foreach (['ShouldBeUnique', 'WithoutOverlapping', 'ProviderImportCheckpoint', 'backoff'] as $guard) {
    if (! str_contains($fetch, $guard)) {
        $errors[] = "Fetch job is missing {$guard}.";
    }
}
if ($errors !== []) {
    fwrite(STDERR, "Provider import orchestration verification failed:\n- ".implode("\n- ", $errors)."\n");
    exit(1);
}
echo "Provider import orchestration verification passed.\n";
