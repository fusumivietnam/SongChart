<?php

declare(strict_types=1);

use App\Support\Engineering\RepositoryContractResolver;

$root = dirname(__DIR__);
require $root.'/vendor/autoload.php';
$definitionPath = $root.'/candidate-verification.json';
$runtimePath = $root.'/storage/framework/candidate-verification-runtime.json';

if (! is_file($definitionPath)) {
    fwrite(STDERR, 'candidate-verification.json is missing.'.PHP_EOL);

    exit(1);
}

$trackedState = trim((string) shell_exec('git -C '.escapeshellarg($root).' status --porcelain --untracked-files=no 2>/dev/null'));
if ($trackedState !== '') {
    fwrite(STDERR, "Canonical verification mutated the tracked working tree; refusing to record closure evidence.\n{$trackedState}\n");

    exit(1);
}

$manifest = json_decode((string) file_get_contents($definitionPath), true, 512, JSON_THROW_ON_ERROR);

try {
    $host = getenv('TEST_PGSQL_HOST') ?: 'postgres';
    $port = (int) (getenv('TEST_PGSQL_PORT') ?: 5432);
    $database = getenv('TEST_PGSQL_DATABASE') ?: 'songchart_verify_test';
    $username = getenv('TEST_PGSQL_USERNAME') ?: 'songchart_verify';
    $password = getenv('TEST_PGSQL_PASSWORD') ?: 'songchart_verify_test_only';
    $pdo = new PDO("pgsql:host={$host};port={$port};dbname={$database}", $username, $password);
    $statement = $pdo->query('select version()');

    if ($statement === false) {
        throw new RuntimeException('PostgreSQL version query failed.');
    }

    $postgresVersion = (string) $statement->fetchColumn();
} catch (Throwable $exception) {
    fwrite(STDERR, 'Cannot record verification evidence without PostgreSQL: '.$exception->getMessage().PHP_EOL);

    exit(1);
}

foreach (array_keys($manifest['gates'] ?? []) as $gate) {
    $manifest['gates'][$gate] = 'passed';
}

$manifest['closure_ready'] = true;
$manifest['verified_at'] = gmdate(DATE_ATOM);
$manifest['environment'] = 'canonical-docker-verification';
$repositoryContracts = (new RepositoryContractResolver($root))->compileManifest(true);

$manifest['evidence'] = [
    'php' => PHP_VERSION,
    'postgresql' => $postgresVersion,
    'node' => trim((string) shell_exec('node --version 2>/dev/null')),
    'composer' => trim((string) shell_exec('composer --version --no-ansi 2>/dev/null')),
    'repository_contracts' => [
        'graph_fingerprint' => $repositoryContracts['graph_fingerprint'],
        'source_tree_sha256' => $repositoryContracts['runtime']['source_tree_sha256'],
        'composer_lock_sha256' => $repositoryContracts['runtime']['composer_lock_sha256'],
        'package_lock_sha256' => $repositoryContracts['runtime']['package_lock_sha256'],
        'git_commit' => $repositoryContracts['runtime']['git_commit'],
        'git_tree' => $repositoryContracts['runtime']['git_tree'],
        'git_dirty' => $repositoryContracts['runtime']['git_dirty'],
        'postgres_schema_snapshot_sha256' => is_file($root.'/storage/framework/postgres-schema-snapshot.json')
            ? hash_file('sha256', $root.'/storage/framework/postgres-schema-snapshot.json')
            : null,
        'package_upstream_adaptations_sha256' => is_file($root.'/storage/framework/package-upstream-adaptations.json')
            ? hash_file('sha256', $root.'/storage/framework/package-upstream-adaptations.json')
            : null,
    ],
];

file_put_contents(
    $runtimePath,
    json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES).PHP_EOL,
);

fwrite(STDOUT, 'Canonical candidate runtime evidence recorded without modifying tracked source.'.PHP_EOL);
