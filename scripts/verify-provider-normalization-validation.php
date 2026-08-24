<?php

declare(strict_types=1);

$required = [
    'app/Domain/Providers/Normalization/Validation/Contracts/NormalizedProviderEntityValidator.php',
    'app/Domain/Providers/Normalization/Validation/DTO/NormalizationValidationIssue.php',
    'app/Domain/Providers/Normalization/Validation/DTO/NormalizationValidationResult.php',
    'app/Domain/Providers/Normalization/Validation/Enums/NormalizationFailureKind.php',
    'app/Support/Providers/Normalization/DefaultNormalizedProviderEntityValidator.php',
    'app/Actions/Providers/Ingestion/RetryQuarantinedProviderImportItem.php',
    'tests/Unit/Providers/NormalizedProviderValidationTest.php',
    'tests/Architecture/ProviderNormalizationValidationBoundaryTest.php',
    'docs/providers/NORMALIZATION_VALIDATION_AND_QUARANTINE.md',
];

foreach ($required as $path) {
    if (! is_file($path)) {
        fwrite(STDERR, "Missing Stage 14.2 authority file: {$path}\n");
        exit(1);
    }
}

$job = file_get_contents('app/Jobs/Providers/Ingestion/ProcessProviderImportPayload.php');
if (! str_contains($job, 'ProviderImportItemStatus::Quarantined') || ! str_contains($job, 'ProviderImportFailureStage::Validation')) {
    fwrite(STDERR, "Normalization job is not wired to quarantine persistence.\n");
    exit(1);
}

echo "Provider normalization validation verification passed.\n";
