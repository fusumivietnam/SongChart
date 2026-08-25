<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$write = in_array('--write', array_slice($argv, 1), true);
$routeAuthority = json_decode((string) file_get_contents($root.'/docs/project/domain/route-authority.json'), true, flags: JSON_THROW_ON_ERROR);
$readme = (string) file_get_contents($root.'/README.md');
$state = (string) file_get_contents($root.'/docs/project/DEVELOPMENT_STATE.md');

preg_match('/Current stage:\s*\*\*([^*]+)\*\*/', $readme, $stageMatch);
preg_match('/Candidate delivery:\s*\*\*([^*]+)\*\*/', $readme, $candidateMatch);

$domainModules = [];
foreach (glob($root.'/app/Domain/*', GLOB_ONLYDIR) ?: [] as $dir) {
    $domainModules[] = basename($dir);
}
sort($domainModules);

$controllers = [];
foreach (glob($root.'/app/Http/Controllers/*', GLOB_ONLYDIR) ?: [] as $dir) {
    $controllers[] = basename($dir);
}
sort($controllers);

$git = static function (array $args) use ($root): ?string {
    $command = 'git -C '.escapeshellarg($root).' '.implode(' ', array_map('escapeshellarg', $args)).' 2>/dev/null';
    $value = trim((string) shell_exec($command));

    return $value === '' ? null : $value;
};

$map = [
    'schema_version' => 1,
    'generated_from_repository' => true,
    'stage' => trim($stageMatch[1] ?? 'unknown'),
    'candidate' => trim($candidateMatch[1] ?? 'unknown'),
    'git' => [
        'commit' => $git(['rev-parse', 'HEAD']),
        'branch' => $git(['branch', '--show-current']),
        'dirty' => (($git(['status', '--porcelain']) ?? '') !== ''),
    ],
    'modules' => [
        'domain' => $domainModules,
        'controller_areas' => $controllers,
    ],
    'routes' => [
        'authority' => 'docs/project/domain/route-authority.json',
        'canonical_families' => array_map('count', $routeAuthority['canonical_families'] ?? []),
        'retired_alias_count' => count($routeAuthority['retired_aliases'] ?? []),
    ],
    'continuation' => [
        'authority_read_order' => [
            'PROJECT_AUTHORITY.md',
            'docs/project/engineering/AI_DEVELOPMENT_PROTOCOL.md',
            'README.md',
            'docs/project/DEVELOPMENT_STATE.md',
            'docs/project/generated/project-context.json',
        ],
        'operational_checkpoint_present' => str_contains($state, '# Development State'),
    ],
];

$json = json_encode($map, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR).PHP_EOL;
if ($write) {
    file_put_contents($root.'/docs/project/generated/development-map.json', $json);
}
fwrite(STDOUT, $json);
