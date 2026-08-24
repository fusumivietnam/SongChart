<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$required = [
    'app/Contracts/Providers/Catalog/ProviderCatalogAdapter.php',
    'app/Contracts/Providers/Catalog/ProviderCatalogAdapterRegistry.php',
    'app/Domain/Providers/Catalog/DTO/ProviderImportContext.php',
    'app/Domain/Providers/Catalog/DTO/ProviderPage.php',
    'app/Domain/Providers/Catalog/DTO/ProviderPayload.php',
    'app/Domain/Providers/Catalog/DTO/NormalizedProviderEntity.php',
    'app/Domain/Providers/Catalog/DTO/ProviderRequestFailure.php',
    'app/Domain/Providers/Catalog/Enums/ProviderCatalogCapability.php',
    'app/Domain/Providers/Catalog/Enums/ProviderRequestFailureKind.php',
    'app/Domain/Providers/Catalog/ValueObjects/ProviderRateLimitState.php',
    'app/Support/Providers/Catalog/InMemoryProviderCatalogAdapterRegistry.php',
    'app/Support/Providers/Catalog/MusicBrainzProviderCatalogAdapter.php',
    'docs/providers/STAGE_17_1_FIRST_LIVE_PROVIDER_CONTRACT.md',
    'docs/providers/PROVIDER_CATALOG_CONTRACTS.md',
];

$errors = [];
foreach ($required as $file) {
    if (! is_file($root.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $file))) {
        $errors[] = "Missing required provider catalog contract file: {$file}";
    }
}

$provider = file_get_contents($root.'/app/Providers/ProviderServiceProvider.php') ?: '';
foreach (['songchart.provider-catalog-adapters', 'ProviderCatalogAdapterRegistry::class', 'MusicBrainzProviderCatalogAdapter::class'] as $needle) {
    if (! str_contains($provider, $needle)) {
        $errors[] = "ProviderServiceProvider must register provider catalog contract boundary: {$needle}";
    }
}

$contract = file_get_contents($root.'/app/Contracts/Providers/Catalog/ProviderCatalogAdapter.php') ?: '';
foreach (['fetchPage(', 'normalize(', 'ProviderPage', 'NormalizedProviderEntity'] as $needle) {
    if (! str_contains($contract, $needle)) {
        $errors[] = "ProviderCatalogAdapter contract is missing {$needle}";
    }
}

if ($errors !== []) {
    fwrite(STDERR, "Provider catalog contract verification failed:\n- ".implode("\n- ", $errors)."\n");
    exit(1);
}

echo "Provider catalog contract verification passed.\n";
