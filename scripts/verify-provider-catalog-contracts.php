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
    'docs/project/domain/provider-reference-matrix.json',
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

try {
    $matrix = json_decode(
        (string) file_get_contents($root.'/docs/project/domain/provider-reference-matrix.json'),
        true,
        flags: JSON_THROW_ON_ERROR,
    );
} catch (Throwable $exception) {
    $matrix = [];
    $errors[] = 'Provider reference matrix must decode as JSON: '.$exception->getMessage();
}

if (($matrix['schema_version'] ?? null) !== 1 || ($matrix['owner'] ?? null) !== 'provider-reference-matrix') {
    $errors[] = 'Provider reference matrix must use schema_version 1 and the canonical owner.';
}
if (! str_contains((string) ($matrix['authority_rule'] ?? ''), 'reference/evidence only')) {
    $errors[] = 'Provider reference matrix must explicitly deny provider schema authority.';
}

$providers = is_array($matrix['providers'] ?? null) ? $matrix['providers'] : [];
foreach (['musicbrainz', 'cover-art-archive', 'wikidata', 'youtube'] as $slug) {
    $entry = $providers[$slug] ?? null;
    if (! is_array($entry) || ($entry['registry_status'] ?? null) !== 'approved') {
        $errors[] = "Provider reference matrix must cover approved provider [{$slug}].";

        continue;
    }
    foreach (['role', 'official_source', 'reference_capabilities', 'operational_constraints', 'canonical_use'] as $requiredField) {
        if (! array_key_exists($requiredField, $entry)) {
            $errors[] = "Provider reference [{$slug}] is missing [{$requiredField}].";
        }
    }
    $source = $entry['official_source'] ?? null;
    if (! is_string($source) || ! str_starts_with($source, 'https://')) {
        $errors[] = "Provider reference [{$slug}] must point to an HTTPS official source.";
    }
}

$identityRule = $matrix['cross_provider_concepts']['canonical_identity']['rule'] ?? '';
if (! is_string($identityRule) || ! str_contains($identityRule, 'never replace SongChart ULID/slug identity')) {
    $errors[] = 'Provider reference matrix must preserve SongChart canonical identity above external identifiers.';
}

$compliance = $matrix['compliance_gate']['required_known_before_new_provider_approval'] ?? [];
foreach (['commercial permission', 'credential eligibility', 'retention/cache rules', 'attribution', 'deletion/revocation', 'media handling', 'current API reference'] as $gate) {
    if (! is_array($compliance) || ! in_array($gate, $compliance, true)) {
        $errors[] = "Provider reference matrix is missing compliance gate [{$gate}].";
    }
}

if ($errors !== []) {
    fwrite(STDERR, "Provider catalog contract verification failed:\n- ".implode("\n- ", array_unique($errors))."\n");
    exit(1);
}

echo "Provider catalog contract verification passed.\n";
