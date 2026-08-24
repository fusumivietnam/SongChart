<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$errors = [];

$required = [
    'database/migrations/2026_08_04_000100_create_canonical_catalog_tables.php',
    'database/migrations/2026_08_04_000200_create_catalog_provenance_mapping_tables.php',
    'database/migrations/2026_08_04_000300_harden_catalog_identity_invariants.php',
    'database/seeders/CatalogFixtureSeeder.php',
    'app/Models/Catalog/Artist.php',
    'app/Models/Catalog/Work.php',
    'app/Models/Catalog/Recording.php',
    'app/Models/Catalog/Release.php',
    'app/Models/Catalog/EntityMatch.php',
    'app/Models/Catalog/MetadataConflict.php',
    'tests/Feature/Catalog/CanonicalCatalogModelTest.php',
    'tests/Feature/Catalog/CatalogInvariantHardeningTest.php',
    'tests/Architecture/CatalogModelGuardrailTest.php',
    'docs/catalog/STAGE_12_CATALOG_MODEL.md',
    'docs/catalog/STAGE_12_1_CLOSURE_REPORT.md',
];

foreach ($required as $path) {
    if (! is_file($root.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $path))) {
        $errors[] = "Required catalog file missing: {$path}";
    }
}

$read = static fn (string $path): string => is_file($root.'/'.$path) ? (string) file_get_contents($root.'/'.$path) : '';
$canonical = $read('database/migrations/2026_08_04_000100_create_canonical_catalog_tables.php');
$provenance = $read('database/migrations/2026_08_04_000200_create_catalog_provenance_mapping_tables.php');
$hardening = $read('database/migrations/2026_08_04_000300_harden_catalog_identity_invariants.php');
$composer = $read('composer.json');
$seeder = $read('database/seeders/DatabaseSeeder.php');
$entityMatch = $read('app/Models/Catalog/EntityMatch.php');
$metadataConflict = $read('app/Models/Catalog/MetadataConflict.php');

foreach (['artists', 'works', 'recordings', 'releases', 'release_tracks', 'recording_versions', 'collections', 'collection_items', 'artist_recording', 'artist_release'] as $table) {
    if (! str_contains($canonical, "Schema::create('{$table}'")) {
        $errors[] = "Canonical table missing: {$table}";
    }
}

foreach (['metadata_sources', 'external_identifiers', 'metadata_assertions', 'metadata_conflicts', 'entity_matches', 'entity_relationships'] as $table) {
    if (! str_contains($provenance, "Schema::create('{$table}'")) {
        $errors[] = "Provenance or mapping table missing: {$table}";
    }
}

$requiredFragments = [
    [$canonical, "unique(['release_id', 'disc_number', 'track_number'])", 'Release position uniqueness missing.'],
    [$canonical, "unique(['collection_id', 'position'])", 'Collection position uniqueness missing.'],
    [$canonical, "unique(['collection_id', 'entity_type', 'entity_id'])", 'Collection member uniqueness missing.'],
    [$provenance, "unique(['namespace', 'value'])", 'External identifier uniqueness missing.'],
    [$provenance, 'metadata_assertion_identity_unique', 'Metadata assertion identity uniqueness missing.'],
    [$provenance, 'provider_canonical_match_unique', 'Provider match tuple uniqueness missing.'],
    [$provenance, 'canonical_relationship_unique', 'Canonical relationship uniqueness missing.'],
    [$hardening, 'metadata_conflict_pair_unique', 'Symmetric metadata conflict uniqueness missing.'],
    [$hardening, 'provider_active_match_unique', 'Active provider match uniqueness missing.'],
    [$entityMatch, 'active_match_key', 'EntityMatch active-key synchronization missing.'],
    [$metadataConflict, 'normalized_pair_key', 'MetadataConflict normalized pair synchronization missing.'],
    [$metadataConflict, 'cannot conflict with itself', 'Metadata self-conflict guard missing.'],
    [$composer, '"@catalog:verify"', 'Catalog verifier is not in the Composer quality chain.'],
    [$seeder, 'CatalogFixtureSeeder::class', 'Catalog fixture seeder is not registered.'],
];

foreach ($requiredFragments as [$haystack, $needle, $message]) {
    if (! str_contains($haystack, $needle)) {
        $errors[] = $message;
    }
}

foreach ([$canonical, $provenance, $hardening] as $migration) {
    if (! str_contains($migration, 'public function down(): void')) {
        $errors[] = 'A Stage 12 migration is missing rollback.';
    }
}

if (str_contains($canonical.$provenance.$hardening, "external_id')->primary")) {
    $errors[] = 'Provider external identifiers must never be canonical primary keys.';
}

if ($errors !== []) {
    foreach ($errors as $error) {
        fwrite(STDERR, $error.PHP_EOL);
    }
    exit(1);
}

echo 'Catalog closure verification passed.'.PHP_EOL;
