<?php

declare(strict_types=1);

$root = dirname(__DIR__);

/** @return array<string, mixed> */
function guidanceReadJson(string $path): array
{
    if (is_file($path) === false) {
        return [];
    }

    try {
        $decoded = json_decode((string) file_get_contents($path), true, flags: JSON_THROW_ON_ERROR);
    } catch (Throwable) {
        return [];
    }

    return is_array($decoded) ? $decoded : [];
}

/**
 * @param  list<string>  $command
 * @return array{output:string,exit_code:int}
 */
function guidanceRun(array $command, string $workingDirectory): array
{
    $pipes = [];
    $process = proc_open(
        $command,
        [
            0 => ['file', '/dev/null', 'r'],
            1 => ['pipe', 'w'],
            2 => ['file', '/dev/null', 'a'],
        ],
        $pipes,
        $workingDirectory,
    );

    if (is_resource($process) === false) {
        return ['output' => '', 'exit_code' => 1];
    }

    $output = stream_get_contents($pipes[1]);
    fclose($pipes[1]);
    $exitCode = proc_close($process);

    return [
        'output' => is_string($output) ? $output : '',
        'exit_code' => $exitCode,
    ];
}

/** @param array<string, mixed> $plan */
function guidanceActiveTranche(array $plan): array
{
    $activeId = (string) ($plan['active_tranche'] ?? '');
    foreach (($plan['stage_progress'] ?? []) as $item) {
        if (is_array($item) && (string) ($item['id'] ?? '') === $activeId) {
            return $item;
        }
    }

    return [];
}

$stateResult = guidanceRun([PHP_BINARY, $root.'/scripts/ai-handoff-status.php'], $root);
if ($stateResult['exit_code'] !== 0) {
    fwrite(STDERR, "Unable to load bounded handoff state.\n");
    exit(1);
}

try {
    $state = json_decode($stateResult['output'], true, flags: JSON_THROW_ON_ERROR);
} catch (Throwable) {
    fwrite(STDERR, "Bounded handoff state is not valid JSON.\n");
    exit(1);
}

if (is_array($state) === false || is_array($state['control_plane'] ?? null) === false) {
    fwrite(STDERR, "Bounded handoff state is missing control-plane authority.\n");
    exit(1);
}

$planPath = 'docs/project/engineering/stage-plan.json';
$topologyPath = 'docs/project/engineering/verification-topology.json';
$surfacePath = 'docs/project/engineering/verification-command-surface.json';
$plan = guidanceReadJson($root.'/'.$planPath);
$topology = guidanceReadJson($root.'/'.$topologyPath);
$surface = guidanceReadJson($root.'/'.$surfacePath);

if ($plan === [] || $topology === [] || $surface === []) {
    fwrite(STDERR, "Unable to resolve guidance authorities.\n");
    exit(1);
}

$tranche = guidanceActiveTranche($plan);
$goals = array_values(array_filter(
    $tranche['goals'] ?? [],
    static fn (mixed $goal): bool => is_string($goal) && $goal !== '',
));
$impactLane = is_array($topology['lanes']['impact'] ?? null) ? $topology['lanes']['impact'] : [];
$publicEntrypoints = is_array($surface['public_entrypoints'] ?? null) ? $surface['public_entrypoints'] : [];
$impactCommands = array_values(array_filter(
    $impactLane['commands'] ?? [],
    static fn (mixed $command): bool => is_string($command) && $command !== '',
));
$focused = array_values(array_filter(
    $publicEntrypoints['focused'] ?? [],
    static fn (mixed $command): bool => is_string($command) && $command !== '',
));
$candidate = array_values(array_filter(
    $publicEntrypoints['candidate_closure'] ?? [],
    static fn (mixed $command): bool => is_string($command) && $command !== '',
));
$canonical = array_values(array_filter(
    $publicEntrypoints['canonical_closure'] ?? [],
    static fn (mixed $command): bool => is_string($command) && $command !== '',
));

$workingTreeClean = ($state['control_plane']['handoff']['working_tree_clean'] ?? false) === true;
$guidanceStatus = $goals === [] ? 'blocked' : 'ready';
$nextActions = [];
foreach ($goals as $goal) {
    $nextActions[] = [
        'type' => 'stage_goal',
        'description' => $goal,
        'source' => $planPath.'#stage_progress/'.$plan['active_tranche'],
        'mutation_allowed' => false,
    ];
}

$nextActions[] = [
    'type' => 'impact_resolution',
    'description' => $workingTreeClean
        ? 'Resolve planned impact before the next source mutation.'
        : 'Resolve the actual repository diff before focused verification.',
    'commands' => $impactCommands,
    'source' => $topologyPath.'#lanes/impact',
    'mutation_allowed' => false,
];

if ($workingTreeClean === false) {
    $nextActions[] = [
        'type' => 'focused_verification',
        'description' => 'Run only repository-authorized focused verification for the current changed surface before closure.',
        'commands' => $focused,
        'source' => $surfacePath.'#public_entrypoints/focused',
        'mutation_allowed' => false,
    ];
}

$state['control_plane']['next_actions'] = [
    'status' => $guidanceStatus,
    'source' => $planPath.' + '.$topologyPath.' + '.$surfacePath,
    'active_tranche' => $plan['active_tranche'] ?? null,
    'actions' => $nextActions,
    'human_gate_required_for_writes' => true,
];
$state['control_plane']['verification_guidance'] = [
    'status' => 'ready',
    'source' => $topologyPath.' + '.$surfacePath,
    'impact' => [
        'owner' => $impactLane['owner'] ?? null,
        'commands' => $impactCommands,
        'rules' => $impactLane['rules'] ?? [],
    ],
    'focused_entrypoints' => $focused,
    'candidate_closure_entrypoints' => $candidate,
    'canonical_closure_entrypoints' => $canonical,
    'exact_head_rule' => 'Resolve live GitHub Auto Closure on the exact PR head before claiming tranche or major-stage acceptance.',
    'writes_remain_human_gated' => true,
];

if ($guidanceStatus === 'blocked') {
    $state['control_plane']['status'] = 'blocked';
}

fwrite(STDOUT, json_encode(
    $state,
    JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR,
).PHP_EOL);
