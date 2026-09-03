<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$args = array_slice($argv, 1);
$jsonOnly = in_array('--json', $args, true);
$write = in_array('--write', $args, true);

/** @return array<string,mixed> */
function readJsonContract(string $path): array
{
    if (! is_file($path)) {
        return [];
    }

    $decoded = json_decode((string) file_get_contents($path), true, flags: JSON_THROW_ON_ERROR);

    return is_array($decoded) ? $decoded : [];
}

/** @return list<string> */
function phpFiles(string $root, string $relative): array
{
    $base = $root.'/'.$relative;
    if (! is_dir($base)) {
        return [];
    }

    $files = [];
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($base, FilesystemIterator::SKIP_DOTS));
    foreach ($iterator as $file) {
        if (! $file instanceof SplFileInfo || ! $file->isFile() || $file->getExtension() !== 'php') {
            continue;
        }
        $files[] = str_replace('\\', '/', substr($file->getPathname(), strlen($root) + 1));
    }
    sort($files);

    return $files;
}

/** @return array{namespace:?string,class:?string,extends:?string,implements:list<string>,uses:list<string>} */
function parseClassShape(string $path): array
{
    $source = (string) file_get_contents($path);
    $namespace = null;
    $class = null;
    $extends = null;
    $implements = [];
    $uses = [];

    if (preg_match('/namespace\s+([^;]+);/', $source, $match) === 1) {
        $namespace = trim($match[1]);
    }
    if (preg_match('/\b(?:final\s+|abstract\s+|readonly\s+)*(?:class|interface|trait|enum)\s+([A-Za-z_][A-Za-z0-9_]*)/', $source, $match) === 1) {
        $class = $match[1];
    }
    if (preg_match('/\bextends\s+([A-Za-z_\x5c][A-Za-z0-9_\x5c]*)/', $source, $match) === 1) {
        $extends = trim($match[1]);
    }
    if (preg_match('/\bimplements\s+([^\{]+)/', $source, $match) === 1) {
        $implements = array_values(array_filter(array_map('trim', explode(',', trim($match[1])))));
    }
    if (preg_match_all('/^use\s+([^;]+);/m', $source, $matches) > 0) {
        foreach ($matches[1] as $use) {
            if (str_contains($use, ' function ') || str_contains($use, ' const ')) {
                continue;
            }
            $uses[] = trim((string) preg_replace('/\s+as\s+.+$/i', '', trim($use)));
        }
    }

    return [
        'namespace' => $namespace,
        'class' => $class,
        'extends' => $extends,
        'implements' => array_values(array_unique($implements)),
        'uses' => array_values(array_unique($uses)),
    ];
}

function qualify(?string $namespace, ?string $name): ?string
{
    if ($name === null || $name === '') {
        return null;
    }
    if (str_starts_with($name, '\\')) {
        return ltrim($name, '\\');
    }
    if (str_contains($name, '\\')) {
        return $name;
    }

    return $namespace !== null && $namespace !== '' ? $namespace.'\\'.$name : $name;
}

/** @return array{nodes:list<array<string,mixed>>,edges:list<array<string,string>>} */
function sourceGraph(string $root): array
{
    $nodes = [];
    $edges = [];
    foreach (phpFiles($root, 'app') as $relative) {
        $shape = parseClassShape($root.'/'.$relative);
        if ($shape['class'] === null) {
            continue;
        }
        $fqcn = qualify($shape['namespace'], $shape['class']);
        if ($fqcn === null) {
            continue;
        }
        $id = 'class:'.$fqcn;
        $nodes[$id] = [
            'id' => $id,
            'type' => 'class',
            'label' => $fqcn,
            'path' => $relative,
        ];
        if ($shape['extends'] !== null) {
            $target = qualify($shape['namespace'], $shape['extends']);
            if ($target !== null) {
                $edges[] = ['from' => $id, 'to' => 'class:'.$target, 'type' => 'extends'];
            }
        }
        foreach ($shape['implements'] as $interface) {
            $target = qualify($shape['namespace'], $interface);
            if ($target !== null) {
                $edges[] = ['from' => $id, 'to' => 'class:'.$target, 'type' => 'implements'];
            }
        }
        foreach ($shape['uses'] as $dependency) {
            $edges[] = ['from' => $id, 'to' => 'class:'.$dependency, 'type' => 'depends_on'];
        }
    }

    foreach (glob($root.'/routes/*.php') ?: [] as $routeFile) {
        $source = (string) file_get_contents($routeFile);
        $relative = str_replace('\\', '/', substr($routeFile, strlen($root) + 1));
        if (preg_match_all('/Route::(get|post|put|patch|delete|options|match|any)\s*\(\s*[\'\"]([^\'\"]+)[\'\"]\s*,\s*([^\n;]+)\)/', $source, $matches, PREG_SET_ORDER) > 0) {
            foreach ($matches as $match) {
                $method = strtoupper($match[1]);
                $uri = $match[2];
                $handler = trim($match[3]);
                $routeId = 'route:'.$method.' '.$uri;
                $nodes[$routeId] = [
                    'id' => $routeId,
                    'type' => 'route',
                    'label' => $method.' '.$uri,
                    'path' => $relative,
                ];
                if (preg_match('/([A-Za-z_\x5c][A-Za-z0-9_\x5c]*)::class/', $handler, $controller) === 1) {
                    $edges[] = ['from' => $routeId, 'to' => 'class:'.$controller[1], 'type' => 'route_to'];
                }
            }
        }
    }

    ksort($nodes);
    usort($edges, static fn (array $a, array $b): int => [$a['from'], $a['type'], $a['to']] <=> [$b['from'], $b['type'], $b['to']]);

    return ['nodes' => array_values($nodes), 'edges' => $edges];
}

/** @return array<string,string> */
function installedPackages(string $root): array
{
    $lock = readJsonContract($root.'/composer.lock');
    $packages = [];
    foreach (array_merge($lock['packages'] ?? [], $lock['packages-dev'] ?? []) as $package) {
        if (! is_array($package) || ! isset($package['name'])) {
            continue;
        }
        $packages[(string) $package['name']] = (string) ($package['version'] ?? 'unknown');
    }
    ksort($packages);

    return $packages;
}

function gitValue(string $root, string $command): ?string
{
    $value = trim((string) shell_exec('cd '.escapeshellarg($root).' && '.$command.' 2>/dev/null'));

    return $value !== '' ? $value : null;
}

try {
    $graphContract = readJsonContract($root.'/docs/project/engineering/architecture-graph-contract.json');
    $kernelContract = readJsonContract($root.'/docs/project/engineering/project-kernel-contract.json');
    if ($graphContract === [] || $kernelContract === []) {
        throw new RuntimeException('Project intelligence contracts are missing or invalid.');
    }

    $headSha = gitValue($root, 'git rev-parse HEAD');
    $branch = gitValue($root, 'git branch --show-current');
    $graph = sourceGraph($root);
    $packages = installedPackages($root);
    $metrics = [
        'class_nodes' => count(array_filter($graph['nodes'], static fn (array $node): bool => $node['type'] === 'class')),
        'route_nodes' => count(array_filter($graph['nodes'], static fn (array $node): bool => $node['type'] === 'route')),
        'edges' => count($graph['edges']),
        'installed_packages' => count($packages),
    ];

    $snapshot = [
        'schema_version' => 1,
        'generated_from_repository' => true,
        'snapshot' => [
            'head_sha' => $headSha,
            'branch' => $branch,
            'status' => 'fresh',
            'consistency' => $graphContract['snapshot_policy']['consistency'] ?? 'eventual',
        ],
        'metrics' => $metrics,
        'graph' => $graph,
        'packages' => $packages,
        'contracts' => [
            'kernel' => 'docs/project/engineering/project-kernel-contract.json',
            'graph' => 'docs/project/engineering/architecture-graph-contract.json',
            'use_case_schema' => 'docs/project/engineering/use-case-contract.schema.json',
            'versioning' => 'docs/project/release/versioning-policy.json',
        ],
    ];

    if ($write) {
        $key = $headSha !== null ? $headSha : 'unknown';
        $directory = $root.'/storage/project-intelligence/'.$key;
        if (! is_dir($directory)) {
            mkdir($directory, 0777, true);
        }
        file_put_contents($directory.'/architecture-graph.json', json_encode($snapshot, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR).PHP_EOL);
        file_put_contents($directory.'/source-metrics.json', json_encode([
            'schema_version' => 1,
            'snapshot' => $snapshot['snapshot'],
            'metrics' => $metrics,
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR).PHP_EOL);
    }

    if ($jsonOnly) {
        fwrite(STDOUT, json_encode($snapshot, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR).PHP_EOL);
        exit(0);
    }

    fwrite(STDOUT, 'SongChart Project Intelligence'.PHP_EOL);
    fwrite(STDOUT, 'Snapshot: '.($headSha ?? 'unknown').' branch='.($branch ?? 'detached').PHP_EOL);
    fwrite(STDOUT, 'Classes: '.$metrics['class_nodes'].' Routes: '.$metrics['route_nodes'].' Edges: '.$metrics['edges'].' Packages: '.$metrics['installed_packages'].PHP_EOL);
    fwrite(STDOUT, 'Machine JSON: php scripts/project-intelligence.php --json'.PHP_EOL);
} catch (Throwable $exception) {
    fwrite(STDERR, 'Unable to build SongChart project intelligence: '.$exception->getMessage().PHP_EOL);
    exit(1);
}
