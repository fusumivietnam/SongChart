<?php

declare(strict_types=1);

$required = [
    'app/Domain/Providers/Identity/Contracts/ExactIdentityResolver.php',
    'app/Domain/Providers/Identity/DTO/IdentityResolutionResult.php',
    'app/Domain/Providers/Identity/Enums/IdentityResolutionOutcome.php',
    'app/Domain/Providers/Identity/Enums/IdentityMatchMethod.php',
    'app/Support/Providers/Identity/EloquentExactIdentityResolver.php',
    'tests/Unit/Providers/ExactIdentityResolutionResultTest.php',
    'tests/Architecture/ProviderExactIdentityBoundaryTest.php',
    'docs/providers/EXACT_IDENTITY_RESOLUTION.md',
];

$errors = [];
foreach ($required as $path) {
    if (! is_file(__DIR__.'/../'.$path)) {
        $errors[] = 'Missing '.$path;
    }
}

$resolver = file_get_contents(__DIR__.'/../app/Support/Providers/Identity/EloquentExactIdentityResolver.php');
foreach (['ExistingMatch', 'ProviderIdentifier', 'ExternalIdentifier', 'IdentifierConflict', 'MatchStatus::NeedsReview'] as $needle) {
    if (! str_contains($resolver, $needle)) {
        $errors[] = 'Resolver is missing '.$needle;
    }
}

if ($errors !== []) {
    fwrite(STDERR, "Exact identity verification failed:\n- ".implode("\n- ", $errors)."\n");
    exit(1);
}

echo "Exact identity verification passed.\n";
