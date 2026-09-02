<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$errors = [];

function canonicalSourceHash(string $path): ?string
{
    if (! is_file($path)) {
        return null;
    }

    $content = (string) file_get_contents($path);
    $content = str_replace(["\r\n", "\r"], "\n", $content);

    return hash('sha256', $content);
}

$required = [
    'songchart',
    'scripts/project-context.php',
    'scripts/project-intelligence.php',
    'app/Console/Commands/ProjectIntelligenceCommand.php',
    'docs/project/engineering/PROJECT_CONTEXT_AUTHORITY.md',
    'docs/project/engineering/project-kernel-contract.json',
    'docs/project/engineering/architecture-graph-contract.json',
    'docs/project/generated/project-context.json',
];
foreach ($required as $relative) {
    if (! is_file($root.'/'.$relative)) {
        $errors[] = "Missing project-context authority file: {$relative}.";
    }
}

$songchart = is_file($root.'/songchart') ? (string) file_get_contents($root.'/songchart') : '';
foreach (['context)', 'scripts/project-context.php', 'Usage: ./songchart dev [setup|ready|up|down|status|logs|shell|url|test|db]'] as $signal) {
    if (! str_contains($songchart, $signal)) {
        $errors[] = "SongChart CLI project-context contract is missing [{$signal}].";
    }
}

$protocol = is_file($root.'/docs/project/engineering/AI_DEVELOPMENT_PROTOCOL.md')
    ? (string) file_get_contents($root.'/docs/project/engineering/AI_DEVELOPMENT_PROTOCOL.md')
    : '';
foreach (['songchart context --json', 'Do not infer class names', 'report drift'] as $signal) {
    if (! str_contains($protocol, $signal)) {
        $errors[] = "AI development protocol project-context rule is missing [{$signal}].";
    }
}

try {
    $ownership = json_decode((string) file_get_contents($root.'/docs/project/domain/schema-ownership.json'), true, flags: JSON_THROW_ON_ERROR);
    $owners = is_array($ownership['tables'] ?? null) ? $ownership['tables'] : [];
    foreach (glob($root.'/database/migrations/*.php') ?: [] as $migration) {
        $source = (string) file_get_contents($migration);
        if (preg_match_all("/Schema::create\\('([^']+)'/", $source, $matches) > 0) {
            foreach ($matches[1] as $table) {
                if (! array_key_exists((string) $table, $owners) && ! in_array((string) $table, $ownership['framework_tables'] ?? [], true)) {
                    $errors[] = "Context source drift: migration table {$table} has no schema owner.";
                }
            }
        }
    }
} catch (Throwable $exception) {
    $errors[] = 'Unable to verify project-context schema sources: '.$exception->getMessage();
}

foreach (glob($root.'/database/seeders/*.php') ?: [] as $seeder) {
    $source = (string) file_get_contents($seeder);
    if (preg_match('/namespace\s+([^;]+);/', $source, $namespace) !== 1 || preg_match('/class\s+([A-Za-z_][A-Za-z0-9_]*)/', $source, $class) !== 1) {
        $errors[] = 'Unable to resolve seeder FQCN from '.basename($seeder).'.';

        continue;
    }
    $fqcn = trim($namespace[1]).'\\'.$class[1];
    if (str_contains($fqcn, '\\\\')) {
        $errors[] = "Seeder FQCN contains doubled namespace separators [{$fqcn}].";
    }
}

$generatedPath = $root.'/docs/project/generated/project-context.json';
if (is_file($generatedPath)) {
    try {
        $generated = json_decode((string) file_get_contents($generatedPath), true, flags: JSON_THROW_ON_ERROR);
        $generatedHashes = is_array($generated['source_hashes'] ?? null) ? $generated['source_hashes'] : [];
        $currentInputs = [
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
            $currentInputs[] = str_replace('\\', '/', substr($migration, strlen($root) + 1));
        }
        foreach (glob($root.'/database/seeders/*.php') ?: [] as $seeder) {
            $currentInputs[] = str_replace('\\', '/', substr($seeder, strlen($root) + 1));
        }
        sort($currentInputs);
        foreach (array_unique($currentInputs) as $relative) {
            $actualHash = canonicalSourceHash($root.'/'.$relative);
            if (($generatedHashes[$relative] ?? null) !== $actualHash) {
                $errors[] = "Generated project context is stale for [{$relative}]. Run: ./songchart candidate --prepare.";
            }
        }
        foreach (array_keys($generatedHashes) as $relative) {
            if (! in_array($relative, $currentInputs, true)) {
                $errors[] = "Generated project context contains obsolete source input [{$relative}].";
            }
        }

        $intelligence = is_array($generated['project_intelligence'] ?? null) ? $generated['project_intelligence'] : [];
        foreach ([
            'compiler' => 'scripts/project-intelligence.php',
            'kernel_contract' => 'docs/project/engineering/project-kernel-contract.json',
            'graph_contract' => 'docs/project/engineering/architecture-graph-contract.json',
            'technology_watchlist' => 'docs/project/engineering/technology-watchlist.json',
            'versioning_policy' => 'docs/project/release/versioning-policy.json',
        ] as $key => $expected) {
            if (($intelligence[$key] ?? null) !== $expected) {
                $errors[] = "Generated project context project-intelligence field [{$key}] is stale or missing.";
            }
        }
        if (($generated['command_surface']['project_intelligence'] ?? null) !== './songchart artisan project:intelligence --json') {
            $errors[] = 'Generated project context must expose the Project Intelligence Artisan command surface.';
        }
        if (($intelligence['request_time_rebuild_forbidden'] ?? null) !== true) {
            $errors[] = 'Generated project context must forbid request-time Project Intelligence rebuilds.';
        }
    } catch (Throwable $exception) {
        $errors[] = 'Unable to validate generated project context: '.$exception->getMessage();
    }
}

if ($errors !== []) {
    fwrite(STDERR, "Project context authority verification failed:\n- ".implode("\n- ", array_unique($errors)).PHP_EOL);
    exit(1);
}

fwrite(STDOUT, 'Project context authority verification passed.'.PHP_EOL);
