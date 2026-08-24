<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$required = [
    'app/Domain/Providers/Normalization/Enums/FieldPresence.php',
    'app/Domain/Providers/Normalization/ValueObjects/ProviderField.php',
    'app/Domain/Providers/Normalization/Contracts/NormalizedEntityData.php',
    'app/Domain/Providers/Normalization/DTO/NormalizedArtist.php',
    'app/Domain/Providers/Normalization/DTO/NormalizedWork.php',
    'app/Domain/Providers/Normalization/DTO/NormalizedRecording.php',
    'app/Domain/Providers/Normalization/DTO/NormalizedRelease.php',
    'app/Domain/Providers/Normalization/DTO/NormalizedIdentifier.php',
    'app/Domain/Providers/Normalization/DTO/NormalizedRelationship.php',
    'app/Domain/Providers/Normalization/DTO/NormalizedEntityDataFactory.php',
    'tests/Unit/Providers/NormalizedProviderDtosTest.php',
    'tests/Architecture/ProviderNormalizationBoundaryTest.php',
    'docs/providers/NORMALIZED_PROVIDER_DTOS.md',
    'docs/foundation/STAGE_14_1_TASK_CONTRACT.md',
];
$errors = [];

foreach ($required as $file) {
    if (! is_file($root.'/'.$file)) {
        $errors[] = "Missing required normalization file: {$file}";
    }
}

$field = file_get_contents($root.'/app/Domain/Providers/Normalization/ValueObjects/ProviderField.php') ?: '';

foreach (['missing()', 'unknown()', 'explicitNull()', 'provided(mixed $value)'] as $needle) {
    if (! str_contains($field, $needle)) {
        $errors[] = "ProviderField missing semantic: {$needle}";
    }
}

$entity = file_get_contents($root.'/app/Domain/Providers/Catalog/DTO/NormalizedProviderEntity.php') ?: '';

if (! str_contains($entity, 'NormalizedEntityData $data')) {
    $errors[] = 'NormalizedProviderEntity must carry typed NormalizedEntityData.';
}

if (str_contains($entity, 'array $attributes')) {
    $errors[] = 'NormalizedProviderEntity must not expose untyped attributes.';
}

if ($errors !== []) {
    fwrite(STDERR, "Provider normalization verification failed:\n- ".implode("\n- ", $errors)."\n");

    exit(1);
}

echo "Provider normalization verification passed.\n";
