<?php

declare(strict_types=1);

use App\Support\ControlPlane\RuntimeProbeRunner;
use Symfony\Component\Process\Process;
use Throwable;

$root = dirname(__DIR__);
require $root.'/vendor/autoload.php';

$stateProcess = new Process([PHP_BINARY, $root.'/scripts/project-state.php', '--json'], $root);
$stateProcess->setTimeout(30);

try {
    $stateProcess->run();
} catch (Throwable) {
    fwrite(STDERR, "Unable to load repository project state.\n");
    exit(1);
}

if (! $stateProcess->isSuccessful()) {
    fwrite(STDERR, "Unable to load repository project state.\n");
    exit(1);
}

try {
    $state = json_decode($stateProcess->getOutput(), true, flags: JSON_THROW_ON_ERROR);
} catch (Throwable) {
    fwrite(STDERR, "Repository project state is not valid JSON.\n");
    exit(1);
}

if (! is_array($state) || ! is_array($state['control_plane'] ?? null)) {
    fwrite(STDERR, "Repository project state is missing control-plane authority.\n");
    exit(1);
}

$runner = new RuntimeProbeRunner;
$probes = [
    'runtime_readiness' => [
        'owner' => 'songchart:doctor',
        'command' => ['./songchart', 'artisan', 'songchart:doctor', '--strict'],
    ],
    'development_database' => [
        'owner' => 'development:database-status',
        'command' => ['./songchart', 'dev', 'db', 'status', '--json'],
    ],
    'development_storage' => [
        'owner' => 'development:storage-status',
        'command' => ['./songchart', 'artisan', 'development:storage-status', '--json'],
    ],
];

$results = [];
foreach ($probes as $name => $probe) {
    $command = $probe['command'];
    $command[0] = $root.'/songchart';
    $results[$name] = [
        ...$runner->run($command, $probe['owner'], $root),
        'command' => implode(' ', $probe['command']),
        'side_effects' => false,
    ];
}

$results['recording_data_trace'] = [
    'status' => 'requires_subject',
    'owner' => 'songchart:data:trace',
    'command' => './songchart artisan songchart:data:trace <recording> --json',
    'requires' => ['recording'],
    'side_effects' => false,
    'secrets_included' => false,
];

$blocked = array_filter(
    $results,
    static fn (array $result): bool => ($result['status'] ?? null) === 'blocked',
);
$runtimeStatus = $blocked === [] ? 'ready' : 'blocked';

$state['control_plane']['runtime'] = [
    'status' => $runtimeStatus,
    'mode' => 'evaluated',
    'read_only' => true,
    'secrets_included' => false,
    'probes' => $results,
    'source' => 'existing SongChart runtime diagnostic owners',
];

foreach ($results as $name => $result) {
    if (isset($state['control_plane']['capabilities'][$name]) && is_array($state['control_plane']['capabilities'][$name])) {
        $state['control_plane']['capabilities'][$name]['status'] = $result['status'];
    }
}

if ($runtimeStatus === 'blocked') {
    $state['control_plane']['status'] = 'blocked';
}

fwrite(STDOUT, json_encode(
    $state,
    JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR,
).PHP_EOL);

exit($runtimeStatus === 'ready' ? 0 : 1);
