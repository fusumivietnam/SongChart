<?php

declare(strict_types=1);

use App\Support\Engineering\RepositoryContractResolver;

$root = dirname(__DIR__);
require_once $root.'/app/Support/Engineering/RepositoryContractResolver.php';
$errors = [];
$composer = json_decode((string) file_get_contents($root.'/composer.json'), true);
$scripts = is_array($composer) ? ($composer['scripts'] ?? []) : [];
$resolver = new RepositoryContractResolver($root);
$contract = $resolver->value('database-test');
$expectedScripts = is_array($contract['scripts'] ?? null) ? $contract['scripts'] : [];

foreach (['test:postgres', 'test:sqlite:compat'] as $script) {
    if (! array_key_exists($script, $scripts)) {
        $errors[] = "Missing database test command: {$script}";
    }
}

foreach (['test', 'test:feature', 'test:postgres'] as $name) {
    $expected = $expectedScripts[$name] ?? null;
    if (! is_string($expected) || ($scripts[$name] ?? null) !== $expected) {
        $errors[] = "{$name} must resolve to the database-test contract authority.";
    }
}

foreach (($contract['clean_schema_required'] ?? []) as $name) {
    $expected = $expectedScripts[$name] ?? '';
    if (! is_string($expected) || ! str_contains($expected, '--prepare-schema')) {
        $errors[] = "{$name} must start from a clean isolated PostgreSQL schema.";
    }
}

$stage = $scripts['stage:verify'] ?? [];
$canonical = $scripts['canonical:verify'] ?? [];
if (! is_array($stage) || ! in_array('@test:postgres', $stage, true)) {
    $errors[] = 'stage:verify must include @test:postgres.';
}
if (is_array($stage) && in_array('@test:sqlite:compat', $stage, true)) {
    $errors[] = 'stage:verify must not use the optional SQLite compatibility lane.';
}
if (! is_array($canonical) || count(array_keys($canonical, '@stage:verify', true)) !== 1) {
    $errors[] = 'canonical:verify must invoke stage:verify exactly once.';
}
if (array_key_exists('release:verify', $scripts) || array_key_exists('verify', $scripts) || array_key_exists('test:all', $scripts)) {
    $errors[] = 'Legacy verification aliases must remain removed.';
}

$postCreate = $scripts['post-create-project-cmd'] ?? [];
if (is_array($postCreate) && str_contains(implode(' ', $postCreate), 'database.sqlite')) {
    $errors[] = 'Project setup must not auto-create database/database.sqlite.';
}

$phpunit = (string) file_get_contents($root.'/phpunit.xml');
if (str_contains($phpunit, 'DB_CONNECTION') || str_contains($phpunit, 'DB_DATABASE')) {
    $errors[] = 'phpunit.xml must not force a database lane.';
}

$runner = (string) file_get_contents($root.'/scripts/run-database-tests.php');
foreach (["['sqlite', 'postgres']", 'TEST_PGSQL_HOST', "DB_CONNECTION'] = 'pgsql'", "DB_CONNECTION'] = 'sqlite'"] as $needle) {
    if (! str_contains($runner, $needle)) {
        $errors[] = 'Database test runner is missing required lane behavior: '.$needle;
    }
}

$workflow = (string) file_get_contents($root.'/.github/workflows/tests.yml');
if (! str_contains($workflow, 'composer test:postgres')) {
    $errors[] = 'CI workflow must use composer test:postgres.';
}
if (str_contains($workflow, 'composer test:sqlite') || str_contains($workflow, 'Tests / SQLite')) {
    $errors[] = 'CI workflow must not treat SQLite as a release test authority.';
}

foreach ([
    'docs/project/governance/database-risk-register.json',
    'docs/project/governance/deletion-retention-matrix.json',
    'docs/project/governance/polymorphic-reference-contract.json',
] as $authority) {
    if (! is_file($root.'/'.$authority)) {
        $errors[] = 'Missing database lifecycle authority: '.$authority;
    }
}

try {
    $riskRegister = json_decode((string) file_get_contents($root.'/docs/project/governance/database-risk-register.json'), true, 512, JSON_THROW_ON_ERROR);
    $retention = json_decode((string) file_get_contents($root.'/docs/project/governance/deletion-retention-matrix.json'), true, 512, JSON_THROW_ON_ERROR);
    $polymorphic = json_decode((string) file_get_contents($root.'/docs/project/governance/polymorphic-reference-contract.json'), true, 512, JSON_THROW_ON_ERROR);
} catch (Throwable $exception) {
    $errors[] = 'Database lifecycle authority JSON is invalid: '.$exception->getMessage();
    $riskRegister = $retention = $polymorphic = [];
}

if (($riskRegister['principles']['canonical_ids'] ?? null) !== 'ulid_and_locked') {
    $errors[] = 'Database risk authority must keep canonical identity strategy locked to the accepted ULID contract.';
}
if (($retention['policy']['soft_delete_is_not_fk_delete'] ?? false) !== true) {
    $errors[] = 'Deletion authority must distinguish soft delete from physical FK delete semantics.';
}
if (($retention['policy']['cascade_requires_child_lifecycle_dependency'] ?? false) !== true) {
    $errors[] = 'Deletion authority must require lifecycle justification for cascade behavior.';
}

$surfaces = $polymorphic['surfaces'] ?? null;
if (! is_array($surfaces) || $surfaces === []) {
    $errors[] = 'Polymorphic reference authority must declare canonical reference surfaces.';
} else {
    $seen = [];
    foreach ($surfaces as $surface) {
        if (! is_array($surface)) {
            $errors[] = 'Polymorphic reference surface must be an object.';
            continue;
        }
        $table = $surface['table'] ?? null;
        $type = $surface['type_column'] ?? null;
        $id = $surface['id_column'] ?? null;
        if (! is_string($table) || $table === '' || ! is_string($type) || $type === '' || ! is_string($id) || $id === '') {
            $errors[] = 'Polymorphic reference surface is incomplete.';
            continue;
        }
        $key = $table.':'.$type.':'.$id;
        if (isset($seen[$key])) {
            $errors[] = 'Duplicate polymorphic reference surface: '.$key;
        }
        $seen[$key] = true;
    }
}

$integritySource = (string) file_get_contents($root.'/app/Support/DomainContracts/PolymorphicReferenceIntegrity.php');
foreach (['EntityType::tryFrom', 'DomainContractRegistry', "where('id'", 'missing_entity_row', 'unknown_entity_type'] as $needle) {
    if (! str_contains($integritySource, $needle)) {
        $errors[] = 'Polymorphic integrity scanner is missing fail-closed behavior: '.$needle;
    }
}

$commandSource = (string) file_get_contents($root.'/app/Console/Commands/VerifyPolymorphicIntegrityCommand.php');
if (! str_contains($commandSource, 'songchart:integrity:polymorphic')) {
    $errors[] = 'Polymorphic integrity command surface is missing.';
}

if ($errors !== []) {
    foreach ($errors as $error) {
        fwrite(STDERR, '[FAIL] '.$error.PHP_EOL);
    }
    exit(1);
}

echo 'PostgreSQL database test and pre-data integrity authority verification passed.'.PHP_EOL;
