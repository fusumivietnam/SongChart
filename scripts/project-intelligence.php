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

function nodeTypeForPath(string $path): string
{
    return match (true) {
        str_starts_with($path, 'app/Http/Controllers/') => 'controller',
        str_starts_with($path, 'app/Jobs/') => 'job',
        str_starts_with($path, 'app/Events/') => 'event',
        str_starts_with($path, 'app/Listeners/') => 'listener',
        str_starts_with($path, 'app/Models/') => 'model',
        str_contains($path, '/Actions/') => 'action',
        str_contains($path, '/Services/') => 'service',
        default => 'class',
    };
}

function semanticOwnerForPath(string $path): ?string
{
    foreach (['app/Application/', 'app/Domain/'] as $prefix) {
        if (! str_starts_with($path, $prefix)) {
            continue;
        }

        $remainder = substr($path, strlen($prefix));
        if ($remainder === false || $remainder === '') {
            return null;
        }

        $context = explode('/', $remainder)[0] ?? '';

        return $context !== '' ? strtolower($context) : null;
    }

    if (str_starts_with($path, 'app/Http/')) {
        return 'http-transport';
    }
    if (str_starts_with($path, 'app/Jobs/')) {
        return 'async-runtime';
    }
    if (str_starts_with($path, 'app/Models/')) {
        return 'persistence';
    }
    if (str_starts_with($path, 'app/Providers/')) {
        return 'framework-bootstrap';
    }
    if (str_starts_with($path, 'app/Console/')) {
        return 'operator-cli';
    }
    if (str_starts_with($path, 'app/Support/')) {
        return 'support-infrastructure';
    }
    if (str_starts_with($path, 'app/Services/')) {
        return 'integration-infrastructure';
    }
    if (str_starts_with($path, 'app/Actions/')) {
        return 'legacy-specialized-action';
    }

    return null;
}

function lifecycleForPath(string $path): string
{
    return match (true) {
        str_starts_with($path, 'app/Providers/') => 'framework_owned',
        str_starts_with($path, 'app/Support/'), str_starts_with($path, 'app/Console/') => 'infrastructure',
        str_starts_with($path, 'app/Services/') => 'infrastructure',
        str_starts_with($path, 'app/Actions/') => 'legacy_supported',
        str_starts_with($path, 'app/Application/'), str_starts_with($path, 'app/Domain/'), str_starts_with($path, 'app/Http/'), str_starts_with($path, 'app/Jobs/'), str_starts_with($path, 'app/Events/'), str_starts_with($path, 'app/Listeners/'), str_starts_with($path, 'app/Models/') => 'active',
        default => 'unclassified',
    };
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
        $semanticOwner = semanticOwnerForPath($relative);
        $nodes[$id] = [
            'id' => $id,
            'type' => nodeTypeForPath($relative),
            'label' => $fqcn,
            'path' => $relative,
            'semantic_owner' => $semanticOwner,
            'lifecycle' => lifecycleForPath($relative),
        ];

        if ($semanticOwner !== null && (str_starts_with($relative, 'app/Application/') || str_starts_with($relative, 'app/Domain/'))) {
            $useCaseId = 'use_case:'.$semanticOwner;
            $nodes[$useCaseId] = [
                'id' => $useCaseId,
                'type' => 'use_case',
                'label' => $semanticOwner,
                'semantic_owner' => $semanticOwner,
                'lifecycle' => 'active',
            ];
            $edges[] = ['from' => $id, 'to' => $useCaseId, 'type' => 'belongs_to_use_case'];
        }

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
                    'semantic_owner' => 'http-transport',
                    'lifecycle' => 'active',
                ];
                if (preg_match('/([A-Za-z_\x5c][A-Za-z0-9_\x5c]*)::class/', $handler, $controller) === 1) {
                    $edges[] = ['from' => $routeId, 'to' => 'class:'.$controller[1], 'type' => 'route_to'];
                }
            }
        }
    }

    $inbound = [];
    $outbound = [];
    foreach ($edges as $edge) {
        $outbound[$edge['from']] = ($outbound[$edge['from']] ?? 0) + 1;
        $inbound[$edge['to']] = ($inbound[$edge['to']] ?? 0) + 1;
    }

    foreach ($nodes as $id => $node) {
        $node['inbound_edges'] = $inbound[$id] ?? 0;
        $node['outbound_edges'] = $outbound[$id] ?? 0;

        if (
            ($node['lifecycle'] ?? null) === 'active'
            && ($node['type'] ?? null) === 'class'
            && str_starts_with((string) ($node['path'] ?? ''), 'app/Application/')
            && $node['inbound_edges'] === 0
        ) {
            $node['lifecycle'] = 'orphan_candidate';
        }

        $nodes[$id] = $node;
    }

    ksort($nodes);
    usort($edges, static fn (array $a, array $b): int => [$a['from'], $a['type'], $a['to']] <=> [$b['from'], $b['type'], $b['to']]);

    return ['nodes' => array_values($nodes), 'edges' => $edges];
}

/** @param list<array<string,mixed>> $nodes @param list<array<string,string>> $edges @return array<string,mixed> */
function connectivityMetrics(array $nodes, array $edges): array
{
    $applicationNodes = array_values(array_filter($nodes, static fn (array $node): bool => str_starts_with((string) ($node['path'] ?? ''), 'app/')));
    $ownedApplicationNodes = array_values(array_filter($applicationNodes, static fn (array $node): bool => is_string($node['semantic_owner'] ?? null) && $node['semantic_owner'] !== ''));
    $orphanCandidates = array_values(array_filter($applicationNodes, static fn (array $node): bool => ($node['lifecycle'] ?? null) === 'orphan_candidate'));
    $unclassified = array_values(array_filter($applicationNodes, static fn (array $node): bool => ($node['lifecycle'] ?? null) === 'unclassified'));
    $routes = array_values(array_filter($nodes, static fn (array $node): bool => ($node['type'] ?? null) === 'route'));
    $jobs = array_values(array_filter($nodes, static fn (array $node): bool => ($node['type'] ?? null) === 'job'));

    $adjacency = [];
    foreach ($edges as $edge) {
        $adjacency[$edge['from']][] = $edge['to'];
    }

    $nodeById = [];
    foreach ($nodes as $node) {
        $nodeById[(string) $node['id']] = $node;
    }

    $reachesUseCase = static function (string $start) use ($adjacency, $nodeById): bool {
        $queue = [$start];
        $seen = [];
        $steps = 0;

        while ($queue !== [] && $steps < 2000) {
            $current = array_shift($queue);
            if (! is_string($current) || isset($seen[$current])) {
                continue;
            }
            $seen[$current] = true;
            $steps++;

            if (($nodeById[$current]['type'] ?? null) === 'use_case') {
                return true;
            }

            foreach ($adjacency[$current] ?? [] as $next) {
                if (! isset($seen[$next])) {
                    $queue[] = $next;
                }
            }
        }

        return false;
    };

    $routeWithUseCase = count(array_filter($routes, static fn (array $node): bool => $reachesUseCase((string) $node['id'])));
    $jobWithUseCase = count(array_filter($jobs, static fn (array $node): bool => $reachesUseCase((string) $node['id']) || is_string($node['semantic_owner'] ?? null)));

    return [
        'application_nodes' => count($applicationNodes),
        'owned_application_nodes' => count($ownedApplicationNodes),
        'orphan_candidate_nodes' => count($orphanCandidates),
        'unclassified_nodes' => count($unclassified),
        'route_to_use_case_coverage' => count($routes) > 0 ? round($routeWithUseCase / count($routes), 4) : 1.0,
        'job_to_use_case_coverage' => count($jobs) > 0 ? round($jobWithUseCase / count($jobs), 4) : 1.0,
    ];
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
    $connectivity = connectivityMetrics($graph['nodes'], $graph['edges']);
    $metrics = [
        'class_nodes' => count(array_filter($graph['nodes'], static fn (array $node): bool => ! in_array($node['type'], ['route', 'use_case'], true))),
        'route_nodes' => count(array_filter($graph['nodes'], static fn (array $node): bool => $node['type'] === 'route')),
        'use_case_nodes' => count(array_filter($graph['nodes'], static fn (array $node): bool => $node['type'] === 'use_case')),
        'edges' => count($graph['edges']),
        'installed_packages' => count($packages),
        'connectivity' => $connectivity,
    ];

    $snapshot = [
        'schema_version' => 2,
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
            'schema_version' => 2,
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
    fwrite(STDOUT, 'Classes: '.$metrics['class_nodes'].' Routes: '.$metrics['route_nodes'].' Use cases: '.$metrics['use_case_nodes'].' Edges: '.$metrics['edges'].' Packages: '.$metrics['installed_packages'].PHP_EOL);
    fwrite(STDOUT, 'Owned application nodes: '.$connectivity['owned_application_nodes'].'/'.$connectivity['application_nodes'].' Orphan candidates: '.$connectivity['orphan_candidate_nodes'].' Unclassified: '.$connectivity['unclassified_nodes'].PHP_EOL);
    fwrite(STDOUT, 'Machine JSON: php scripts/project-intelligence.php --json'.PHP_EOL);
} catch (Throwable $exception) {
    fwrite(STDERR, 'Unable to build SongChart project intelligence: '.$exception->getMessage().PHP_EOL);
    exit(1);
}
