<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$args = array_slice($argv, 1);
$jsonOnly = in_array('--json', $args, true);
$write = in_array('--write', $args, true);
$writeSource = in_array('--write-source', $args, true);
$noRuntime = in_array('--no-runtime', $args, true) || $writeSource;

/** @return array<string,mixed> */
function readJson(string $path): array
{
    if (! is_file($path)) {
        return [];
    }

    $decoded = json_decode((string) file_get_contents($path), true, flags: JSON_THROW_ON_ERROR);

    return is_array($decoded) ? $decoded : [];
}

function canonicalSourceHash(string $path): ?string
{
    if (! is_file($path)) {
        return null;
    }

    $content = (string) file_get_contents($path);
    $content = str_replace(["\r\n", "\r"], "\n", $content);

    return hash('sha256', $content);
}

/** @return list<string> */
function composeServices(string $path): array
{
    if (! is_file($path)) {
        return [];
    }

    $services = [];
    $inside = false;
    foreach (preg_split('/\R/', (string) file_get_contents($path)) ?: [] as $line) {
        if ($line === 'services:') {
            $inside = true;

            continue;
        }
        if ($inside && preg_match('/^[A-Za-z0-9_.-]+:/', $line) === 1) {
            break;
        }
        if ($inside && preg_match('/^  ([A-Za-z0-9_.-]+):\s*$/', $line, $match) === 1) {
            $services[] = $match[1];
        }
    }

    sort($services);

    return array_values(array_unique($services));
}

/** @return list<string> */
function devCommands(string $path): array
{
    if (! is_file($path)) {
        return [];
    }

    $source = (string) file_get_contents($path);
    if (preg_match('/Usage: \.\/songchart dev \[([^\]]+)\]/', $source, $match) !== 1) {
        return [];
    }

    return array_values(array_filter(explode('|', $match[1]), static fn (string $value): bool => $value !== ''));
}

/** @return array<string,string> */
function installedPackages(string $lockPath, array $wanted): array
{
    $lock = readJson($lockPath);
    $packages = array_merge($lock['packages'] ?? [], $lock['packages-dev'] ?? []);
    $versions = [];
    foreach ($packages as $package) {
        if (! is_array($package)) {
            continue;
        }
        $name = (string) ($package['name'] ?? '');
        if (in_array($name, $wanted, true)) {
            $versions[$name] = (string) ($package['version'] ?? 'unknown');
        }
    }
    ksort($versions);

    return $versions;
}

/** @return array<string,array{file:string,class:string}> */
function seederRegistry(string $root): array
{
    $seeders = [];
    foreach (glob($root.'/database/seeders/*.php') ?: [] as $file) {
        $source = (string) file_get_contents($file);
        if (preg_match('/namespace\s+([^;]+);/', $source, $namespace) !== 1 || preg_match('/class\s+([A-Za-z_][A-Za-z0-9_]*)/', $source, $class) !== 1) {
            continue;
        }
        $seeders[$class[1]] = [
            'file' => str_replace('\\', '/', substr($file, strlen($root) + 1)),
            'class' => trim($namespace[1]).'\\'.$class[1],
        ];
    }
    ksort($seeders);

    return $seeders;
}

/** @return array<string,array{migration:string,columns:list<string>}> */
function migrationSchema(string $root): array
{
    $tables = [];
    foreach (glob($root.'/database/migrations/*.php') ?: [] as $file) {
        $source = (string) file_get_contents($file);
        if (preg_match_all("/Schema::create\\('([^']+)'\\s*,\\s*function\\s*\\([^)]*\\)(?:\\s*:\\s*void)?\\s*\\{(.*?)\\n\\s*\\}\\);/s", $source, $creates, PREG_SET_ORDER) === 0) {
            continue;
        }
        foreach ($creates as $create) {
            $body = (string) $create[2];
            $columns = [];

            if (preg_match('/\$table->id\(\s*\)/', $body) === 1) {
                $columns[] = 'id';
            }

            $columnPattern = <<<'REGEX'
~\$table->([A-Za-z_][A-Za-z0-9_]*)\(\s*['"]([^'"]+)['"]~
REGEX;
            if (preg_match_all($columnPattern, $body, $matches, PREG_SET_ORDER) > 0) {
                foreach ($matches as $match) {
                    if (! in_array($match[1], ['morphs', 'nullableMorphs'], true)) {
                        $columns[] = (string) $match[2];
                    }
                }
            }

            $morphPattern = <<<'REGEX'
~\$table->(?:morphs|nullableMorphs)\(\s*['"]([^'"]+)['"]~
REGEX;
            if (preg_match_all($morphPattern, $body, $matches) > 0) {
                foreach ($matches[1] as $name) {
                    $columns[] = $name.'_type';
                    $columns[] = $name.'_id';
                }
            }

            if (preg_match('/\$table->timestamps\(\s*\)/', $body) === 1) {
                $columns[] = 'created_at';
                $columns[] = 'updated_at';
            }
            $tables[(string) $create[1]] = [
                'migration' => basename($file),
                'columns' => array_values(array_unique($columns)),
            ];
        }
    }
    ksort($tables);

    return $tables;
}

/** @return array<string,mixed> */
function runtimeDatabaseState(array $expectedTables): array
{
    if (! extension_loaded('pdo_pgsql')) {
        return ['available' => false, 'reason' => 'pdo_pgsql is unavailable.'];
    }

    $host = (string) (getenv('DB_HOST') ?: '');
    $database = (string) (getenv('DB_DATABASE') ?: '');
    $username = (string) (getenv('DB_USERNAME') ?: '');
    $password = (string) (getenv('DB_PASSWORD') ?: '');
    $port = (int) (getenv('DB_PORT') ?: 5432);
    if ($host === '' || $database === '' || $username === '') {
        return ['available' => false, 'reason' => 'PostgreSQL runtime connection is unavailable.'];
    }

    try {
        $pdo = new PDO("pgsql:host={$host};port={$port};dbname={$database}", $username, $password, [PDO::ATTR_TIMEOUT => 2]);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $version = (string) $pdo->query('show server_version')->fetchColumn();
        $rows = $pdo->query('select table_name, column_name from information_schema.columns where table_schema = current_schema() order by table_name, ordinal_position')->fetchAll(PDO::FETCH_ASSOC);
        $actual = [];
        foreach ($rows as $row) {
            $actual[(string) $row['table_name']][] = (string) $row['column_name'];
        }
        $missingTables = [];
        $missingColumns = [];
        foreach ($expectedTables as $table => $definition) {
            if (! array_key_exists($table, $actual)) {
                $missingTables[] = $table;

                continue;
            }
            foreach ($definition['columns'] ?? [] as $column) {
                if (! in_array($column, $actual[$table], true)) {
                    $missingColumns[] = $table.'.'.$column;
                }
            }
        }

        return [
            'available' => true,
            'server_version' => $version,
            'database' => $database,
            'drift' => [
                'missing_tables' => $missingTables,
                'missing_columns' => $missingColumns,
                'clean' => $missingTables === [] && $missingColumns === [],
            ],
        ];
    } catch (Throwable $exception) {
        return ['available' => false, 'reason' => $exception->getMessage()];
    }
}

try {
    $runtimeAuthority = readJson($root.'/docs/project/stack/runtime-environments.json');
    $ownership = readJson($root.'/docs/project/domain/schema-ownership.json');
    $candidate = readJson($root.'/candidate-verification.json');
    $kernelContract = readJson($root.'/docs/project/engineering/project-kernel-contract.json');
    $graphContract = readJson($root.'/docs/project/engineering/architecture-graph-contract.json');
    $technologyWatchlist = readJson($root.'/docs/project/engineering/technology-watchlist.json');
    $versioningPolicy = readJson($root.'/docs/project/release/versioning-policy.json');
    $migrations = migrationSchema($root);
    $owners = is_array($ownership['tables'] ?? null) ? $ownership['tables'] : [];
    $frameworkTables = is_array($ownership['framework_tables'] ?? null) ? $ownership['framework_tables'] : [];

    $tableManifest = [];
    foreach ($migrations as $table => $definition) {
        $tableManifest[$table] = $definition + [
            'owner' => $owners[$table] ?? (in_array($table, $frameworkTables, true) ? 'framework' : null),
        ];
    }

    $sourceFiles = [
        'composer.json',
        'composer.lock',
        'compose.dev.yml',
        'compose.verify.yml',
        'songchart',
        'app/Console/Commands/ProjectIntelligenceCommand.php',
        'scripts/project-intelligence.php',
        'docs/project/stack/runtime-environments.json',
        'docs/project/domain/schema-ownership.json',
        'docs/project/engineering/AI_DEVELOPMENT_PROTOCOL.md',
        'docs/project/engineering/ai-development-contract.json',
        'docs/project/engineering/PROJECT_CONTEXT_AUTHORITY.md',
        'docs/project/engineering/project-knowledge.json',
        'docs/project/engineering/consolidation-plan.json',
        'docs/project/engineering/project-kernel-contract.json',
        'docs/project/engineering/architecture-graph-contract.json',
        'docs/project/engineering/use-case-contract.schema.json',
        'docs/project/engineering/mcp-contract.json',
        'docs/project/engineering/development-intelligence-contract.json',
        'docs/project/engineering/technology-evaluation-contract.json',
        'docs/project/engineering/technology-watchlist.json',
        'docs/project/engineering/verification-command-surface.json',
        'docs/project/engineering/verification-topology.json',
        'docs/project/release/versioning-policy.json',
        'docs/project/stack/release-pipeline-contract.json',
    ];
    foreach (glob($root.'/database/migrations/*.php') ?: [] as $migration) {
        $sourceFiles[] = str_replace('\\', '/', substr($migration, strlen($root) + 1));
    }
    foreach (glob($root.'/database/seeders/*.php') ?: [] as $seeder) {
        $sourceFiles[] = str_replace('\\', '/', substr($seeder, strlen($root) + 1));
    }
    sort($sourceFiles);

    $hashes = [];
    foreach (array_unique($sourceFiles) as $relative) {
        $hash = canonicalSourceHash($root.'/'.$relative);
        if ($hash !== null) {
            $hashes[$relative] = $hash;
        }
    }

    $manifest = [
        'schema_version' => 2,
        'generated_from_repository' => true,
        'source_fingerprint' => hash('sha256', json_encode($hashes, JSON_THROW_ON_ERROR)),
        'candidate' => [
            'stage' => $candidate['stage'] ?? null,
            'candidate' => $candidate['candidate'] ?? null,
            'informational_not_fingerprinted' => true,
        ],
        'runtime_authority' => [
            'php' => $runtimeAuthority['release_authority']['php_major_minor'] ?? null,
            'postgres_major' => $runtimeAuthority['release_authority']['postgres_major'] ?? null,
            'database' => $runtimeAuthority['release_authority']['database'] ?? null,
            'primary_development_profile' => $runtimeAuthority['primary_development_profile'] ?? null,
        ],
        'installed_versions' => installedPackages($root.'/composer.lock', [
            'laravel/framework',
            'laravel/pulse',
            'laravel/boost',
            'livewire/livewire',
            'spatie/laravel-activitylog',
            'larastan/larastan',
            'pestphp/pest',
        ]),
        'docker' => [
            'development_compose' => 'compose.dev.yml',
            'development_services' => composeServices($root.'/compose.dev.yml'),
            'verification_compose' => 'compose.verify.yml',
            'verification_services' => composeServices($root.'/compose.verify.yml'),
            'canonical_project' => 'songchart-verify',
            'https_url' => 'https://docker.songchart.test:8443',
        ],
        'command_surface' => [
            'dev' => devCommands($root.'/songchart'),
            'canonical_verify' => './songchart verify',
            'context' => './songchart context --json',
            'project_intelligence' => './songchart artisan project:intelligence --json',
            'project_intelligence_refresh' => './songchart artisan project:intelligence --write',
        ],
        'project_intelligence' => [
            'compiler' => 'scripts/project-intelligence.php',
            'artisan_command' => 'project:intelligence',
            'kernel_contract' => 'docs/project/engineering/project-kernel-contract.json',
            'graph_contract' => 'docs/project/engineering/architecture-graph-contract.json',
            'use_case_schema' => 'docs/project/engineering/use-case-contract.schema.json',
            'mcp_contract' => 'docs/project/engineering/mcp-contract.json',
            'development_intelligence_contract' => 'docs/project/engineering/development-intelligence-contract.json',
            'technology_watchlist' => 'docs/project/engineering/technology-watchlist.json',
            'versioning_policy' => 'docs/project/release/versioning-policy.json',
            'snapshot_consistency' => $graphContract['snapshot_policy']['consistency'] ?? 'eventual',
            'request_time_rebuild_forbidden' => true,
            'kernel_status' => $kernelContract['status'] ?? null,
            'technology_evaluated_at' => $technologyWatchlist['evaluated_at'] ?? null,
            'product_version_format' => $versioningPolicy['product_version']['format'] ?? null,
        ],
        'seeders' => seederRegistry($root),
        'database' => [
            'authority' => 'postgresql',
            'tables' => $tableManifest,
            'ownership_complete' => count(array_filter($tableManifest, static fn (array $row): bool => $row['owner'] === null)) === 0,
        ],
        'source_hashes' => $hashes,
    ];

    if (! $noRuntime) {
        $manifest['database']['runtime'] = runtimeDatabaseState($tableManifest);
    }

    if ($write || $writeSource) {
        $target = $writeSource
            ? $root.'/docs/project/generated/project-context.json'
            : $root.'/storage/project-state/project-context.json';
        if (! is_dir(dirname($target))) {
            mkdir(dirname($target), 0777, true);
        }
        file_put_contents($target, json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR).PHP_EOL);
    }

    if ($jsonOnly) {
        fwrite(STDOUT, json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR).PHP_EOL);
        exit(0);
    }

    $runtime = $manifest['database']['runtime'] ?? ['available' => false, 'reason' => 'runtime inspection disabled'];
    fwrite(STDOUT, "SongChart Project Context v2\n");
    fwrite(STDOUT, 'Candidate: Stage '.($manifest['candidate']['stage'] ?? 'unknown').' / '.($manifest['candidate']['candidate'] ?? 'unknown')."\n");
    fwrite(STDOUT, 'Runtime authority: PHP '.($manifest['runtime_authority']['php'] ?? '?').', PostgreSQL '.($manifest['runtime_authority']['postgres_major'] ?? '?')."\n");
    fwrite(STDOUT, 'Dev Compose: compose.dev.yml ['.implode(', ', $manifest['docker']['development_services'])."]\n");
    fwrite(STDOUT, 'Canonical Compose project: songchart-verify'."\n");
    fwrite(STDOUT, 'Dev commands: '.implode(', ', $manifest['command_surface']['dev'])."\n");
    fwrite(STDOUT, 'Project Intelligence: '.$manifest['command_surface']['project_intelligence']."\n");
    fwrite(STDOUT, 'Migration-owned tables: '.count($tableManifest)."\n");
    fwrite(STDOUT, 'Schema ownership: '.($manifest['database']['ownership_complete'] ? 'complete' : 'DRIFT')."\n");
    fwrite(STDOUT, 'PostgreSQL runtime: '.(($runtime['available'] ?? false) ? (($runtime['database'] ?? '?').' @ '.($runtime['server_version'] ?? '?')) : 'unavailable ('.($runtime['reason'] ?? 'unknown').')')."\n");
    fwrite(STDOUT, 'Machine JSON: ./songchart context --json'."\n");
} catch (Throwable $exception) {
    fwrite(STDERR, 'Unable to build SongChart project context: '.$exception->getMessage().PHP_EOL);
    exit(1);
}
