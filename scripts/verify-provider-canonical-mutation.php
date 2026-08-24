<?php

declare(strict_types=1);

$required = [
    'app/Domain/Providers/Mutation/Contracts/CanonicalMutationAction.php',
    'app/Domain/Providers/Mutation/Contracts/CanonicalMutationPipeline.php',
    'app/Domain/Providers/Mutation/DTO/CanonicalMutationResult.php',
    'app/Domain/Providers/Mutation/Enums/CanonicalMutationOutcome.php',
    'app/Support/Providers/Mutation/EloquentCanonicalMutationAction.php',
    'app/Support/Providers/Mutation/DefaultCanonicalMutationPipeline.php',
    'tests/Unit/Providers/CanonicalMutationResultTest.php',
    'tests/Architecture/ProviderCanonicalMutationBoundaryTest.php',
    'docs/providers/CANONICAL_MUTATION_ACTIONS.md',
];

$errors = [];
foreach ($required as $file) {
    if (! is_file($file)) {
        $errors[] = "Missing required Stage 14.3 file: {$file}";
    }
}

$provider = file_get_contents('app/Providers/ProviderServiceProvider.php') ?: '';
$job = file_get_contents('app/Jobs/Providers/Ingestion/ProcessProviderImportPayload.php') ?: '';
if (! str_contains($provider, 'songchart.canonical-mutation-actions')) {
    $errors[] = 'Canonical mutation actions are not registered through a container tag.';
}
if (! str_contains($job, 'CanonicalMutationPipeline')) {
    $errors[] = 'Provider import processing does not invoke the canonical mutation pipeline.';
}
if ($errors !== []) {
    fwrite(STDERR, "Provider canonical mutation verification failed:\n- ".implode("\n- ", $errors)."\n");
    exit(1);
}

echo "Provider canonical mutation verification passed.\n";
