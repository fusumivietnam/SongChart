<?php

declare(strict_types=1);

use App\Support\Engineering\RepositoryContractResolver;

$root = dirname(__DIR__);
require_once $root.'/app/Support/Engineering/RepositoryContractResolver.php';

$args = array_values(array_slice($argv, 1));
$useDiff = false;
$json = false;
$paths = [];

foreach ($args as $arg) {
    if ($arg === '--diff') {
        $useDiff = true;

        continue;
    }
    if ($arg === '--json') {
        $json = true;

        continue;
    }
    if ($arg !== '') {
        $paths[] = normalizePath($arg);
    }
}

if ($useDiff) {
    $paths = array_values(array_unique(array_merge($paths, changedPaths($root))));
}

if ($paths === []) {
    fwrite(STDERR, "Usage: php scripts/resolve-repository-impact.php [--diff] [--json] <changed-path> [changed-path...]\n");
    exit(2);
}

$resolver = new RepositoryContractResolver($root);
$impactMap = readJson($root.'/docs/project/stack/impact-test-map.json');
$verificationConsumers = $resolver->verificationConsumers();
$consumerByTarget = [];
foreach ($verificationConsumers as $consumer) {
    $consumerByTarget[$consumer['target']] = $consumer;
}

$requiredChecks = [];
$matchedRules = [];
foreach (($impactMap['rules'] ?? []) as $index => $rule) {
    if (! is_array($rule)) {
        continue;
    }

    $patterns = array_values(array_filter((array) ($rule['paths'] ?? []), 'is_string'));
    $matchedPaths = [];
    foreach ($paths as $path) {
        foreach ($patterns as $pattern) {
            if (pathMatches($pattern, $path)) {
                $matchedPaths[] = $path;
                break;
            }
        }
    }

    if ($matchedPaths === []) {
        continue;
    }

    $name = (string) ($rule['name'] ?? 'rule-'.($index + 1));
    $checks = array_values(array_filter((array) ($rule['required_tests'] ?? []), 'is_string'));
    $matchedRules[] = [
        'name' => $name,
        'paths' => array_values(array_unique($matchedPaths)),
        'required_checks' => $checks,
    ];
    $requiredChecks = array_merge($requiredChecks, $checks);
}
$requiredChecks = array_values(array_unique($requiredChecks));

$impactedAuthorities = $resolver->impactedAuthorities($paths);
foreach ($requiredChecks as $check) {
    if (isset($consumerByTarget[$check])) {
        $impactedAuthorities = array_merge($impactedAuthorities, $consumerByTarget[$check]['semantic_authorities']);
    }
}
$impactedAuthorities = array_values(array_unique($impactedAuthorities));
sort($impactedAuthorities);

$reverseConsumers = [];
foreach ($verificationConsumers as $consumer) {
    if (array_intersect($consumer['semantic_authorities'], $impactedAuthorities) === []) {
        continue;
    }
    $reverseConsumers[] = $consumer;
}
usort($reverseConsumers, static fn (array $left, array $right): int => $left['target'] <=> $right['target']);

$result = [
    'mode' => $useDiff ? 'actual-diff' : 'planned-paths',
    'changed_paths' => $paths,
    'matched_impact_rules' => $matchedRules,
    'impacted_authorities' => array_map(static function (string $authority) use ($resolver): array {
        $definition = $resolver->authority($authority);

        return [
            'name' => $authority,
            'source' => $definition['source'],
            'registered_consumers' => $definition['consumers'],
        ];
    }, $impactedAuthorities),
    'reverse_verification_consumers' => $reverseConsumers,
    'required_focused_checks' => $requiredChecks,
];

if ($json) {
    fwrite(STDOUT, json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR).PHP_EOL);
    exit(0);
}

fwrite(STDOUT, '[SongChart impact] Mode: '.$result['mode'].PHP_EOL);
fwrite(STDOUT, "Changed paths:\n");
foreach ($paths as $path) {
    fwrite(STDOUT, "- {$path}\n");
}

fwrite(STDOUT, "\nMatched impact rules:\n");
if ($matchedRules === []) {
    fwrite(STDOUT, "- none\n");
} else {
    foreach ($matchedRules as $rule) {
        fwrite(STDOUT, '- '.$rule['name'].' => '.implode(', ', $rule['paths']).PHP_EOL);
    }
}

fwrite(STDOUT, "\nImpacted authorities:\n");
if ($result['impacted_authorities'] === []) {
    fwrite(STDOUT, "- none resolved\n");
} else {
    foreach ($result['impacted_authorities'] as $authority) {
        fwrite(STDOUT, "- {$authority['name']} [{$authority['source']}]\n");
    }
}

fwrite(STDOUT, "\nReverse verification consumers:\n");
if ($reverseConsumers === []) {
    fwrite(STDOUT, "- none resolved\n");
} else {
    foreach ($reverseConsumers as $consumer) {
        fwrite(STDOUT, "- {$consumer['target']} => {$consumer['rule']} [{$consumer['classification']}]\n");
    }
}

fwrite(STDOUT, "\nRequired focused checks:\n");
if ($requiredChecks === []) {
    fwrite(STDOUT, "- none resolved\n");
} else {
    foreach ($requiredChecks as $check) {
        fwrite(STDOUT, "- {$check}\n");
    }
}

/** @return array<string, mixed> */
function readJson(string $path): array
{
    $decoded = json_decode((string) file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);
    if (! is_array($decoded)) {
        throw new RuntimeException("Expected JSON object at {$path}.");
    }

    return $decoded;
}

function normalizePath(string $path): string
{
    $path = str_replace('\\', '/', trim($path));

    return str_starts_with($path, './') ? substr($path, 2) : $path;
}

function pathMatches(string $pattern, string $path): bool
{
    $pattern = normalizePath($pattern);
    $path = normalizePath($path);

    return fnmatch($pattern, $path) || $pattern === $path;
}

/** @return list<string> */
function changedPaths(string $root): array
{
    $base = resolveImpactBase($root);
    $commands = [];
    if ($base !== null) {
        $commands[] = 'git -C '.escapeshellarg($root).' diff --name-only --diff-filter=ACMRD '.escapeshellarg($base).'...HEAD';
    }
    $commands[] = 'git -C '.escapeshellarg($root).' diff --name-only --diff-filter=ACMRD HEAD';
    $commands[] = 'git -C '.escapeshellarg($root).' ls-files --others --exclude-standard';

    $paths = [];
    foreach ($commands as $command) {
        exec($command, $output, $status);
        if ($status !== 0) {
            $output = [];

            continue;
        }
        foreach ($output as $path) {
            $path = normalizePath($path);
            if ($path !== '') {
                $paths[] = $path;
            }
        }
        $output = [];
    }

    return array_values(array_unique($paths));
}

function resolveImpactBase(string $root): ?string
{
    $requested = trim((string) getenv('SONGCHART_IMPACT_BASE'));
    $candidates = array_values(array_filter([$requested, 'origin/main', 'main'], static fn (string $value): bool => $value !== ''));

    foreach ($candidates as $candidate) {
        $command = 'git -C '.escapeshellarg($root).' rev-parse --verify --quiet '.escapeshellarg($candidate).'^{commit}';
        exec($command, $output, $status);
        if ($status !== 0) {
            $output = [];

            continue;
        }

        $mergeBase = trim((string) shell_exec('git -C '.escapeshellarg($root).' merge-base HEAD '.escapeshellarg($candidate).' 2>/dev/null'));
        if ($mergeBase !== '') {
            return $mergeBase;
        }
    }

    return null;
}
