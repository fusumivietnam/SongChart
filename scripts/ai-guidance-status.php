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

/**
 * @param  array<string, mixed>  $publicEntrypoints
 * @return list<string>
 */
function guidanceEntrypoints(array $publicEntrypoints, string $group): array
{
    return array_values(array_filter(
        $publicEntrypoints[$group] ?? [],
        static fn (mixed $command): bool => is_string($command) && $command !== '',
    ));
}

/**
 * @param  list<string>  $registered
 * @param  list<string>  $preferred
 * @return list<string>
 */
function guidanceRegisteredCommands(array $registered, array $preferred): array
{
    return array_values(array_filter(
        $preferred,
        static fn (string $command): bool => in_array($command, $registered, true),
    ));
}

/** @return array<string, mixed> */
function guidanceImpactResolution(string $root): array
{
    $result = guidanceRun([
        PHP_BINARY,
        $root.'/scripts/resolve-repository-impact.php',
        '--diff',
        '--json',
    ], $root);

    if ($result['exit_code'] !== 0) {
        return [];
    }

    try {
        $decoded = json_decode($result['output'], true, flags: JSON_THROW_ON_ERROR);
    } catch (Throwable) {
        return [];
    }

    return is_array($decoded) ? $decoded : [];
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
$impactMapPath = 'docs/project/stack/impact-test-map.json';
$impactResolverPath = 'scripts/resolve-repository-impact.php';
$impactRunnerPath = 'scripts/run-impact-verification.sh';
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
$development = guidanceEntrypoints($publicEntrypoints, 'development');
$focused = guidanceEntrypoints($publicEntrypoints, 'focused');
$candidate = guidanceEntrypoints($publicEntrypoints, 'candidate_closure');
$canonical = guidanceEntrypoints($publicEntrypoints, 'canonical_closure');

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

$operationBundles = [
    'orient' => [
        'status' => 'ready',
        'purpose' => 'Resolve deterministic repository, stage and handoff context before coding.',
        'commands' => guidanceRegisteredCommands($development, ['songchart ai status', 'songchart ai doctor']),
        'source' => $surfacePath.'#public_entrypoints/development',
        'mutation_allowed' => false,
        'human_gate_required_for_writes' => true,
    ],
    'implement' => [
        'status' => $guidanceStatus,
        'purpose' => 'Resolve the current authored tranche goals and repository impact before source changes.',
        'stage' => $plan['current_stage']['id'] ?? null,
        'active_tranche' => $plan['active_tranche'] ?? null,
        'goals' => $goals,
        'commands' => guidanceRegisteredCommands($development, ['songchart impact', 'songchart impact --diff']),
        'source' => $planPath.' + '.$surfacePath.'#public_entrypoints/development',
        'mutation_allowed' => false,
        'human_gate_required_for_writes' => true,
    ],
    'verify' => [
        'status' => $focused === [] ? 'blocked' : 'ready',
        'purpose' => 'Use only registered focused verification entrypoints for the resolved change surface.',
        'commands' => $focused,
        'source' => $surfacePath.'#public_entrypoints/focused',
        'mutation_allowed' => false,
        'human_gate_required_for_writes' => true,
    ],
    'close' => [
        'status' => ($candidate === [] || $canonical === []) ? 'blocked' : 'ready',
        'purpose' => 'Run existing candidate and canonical closure owners, then resolve exact-head GitHub evidence before acceptance.',
        'candidate_commands' => $candidate,
        'canonical_commands' => $canonical,
        'source' => $surfacePath.'#public_entrypoints/candidate_closure + '.$surfacePath.'#public_entrypoints/canonical_closure',
        'exact_head_rule' => 'Resolve live GitHub Auto Closure on the exact PR head before claiming tranche or major-stage acceptance.',
        'mutation_allowed' => false,
        'human_gate_required_for_writes' => true,
    ],
];

$impactResolution = guidanceImpactResolution($root);
$resolvedChecks = array_values(array_filter(
    $impactResolution['required_focused_checks'] ?? [],
    static fn (mixed $check): bool => is_string($check) && $check !== '',
));
$matchedRuleNames = [];
foreach (($impactResolution['matched_impact_rules'] ?? []) as $rule) {
    if (is_array($rule) && is_string($rule['name'] ?? null) && $rule['name'] !== '') {
        $matchedRuleNames[] = $rule['name'];
    }
}
$impactedAuthorityNames = [];
foreach (($impactResolution['impacted_authorities'] ?? []) as $authority) {
    if (is_array($authority) && is_string($authority['name'] ?? null) && $authority['name'] !== '') {
        $impactedAuthorityNames[] = $authority['name'];
    }
}
$focusedExecutionEntrypoints = guidanceRegisteredCommands($focused, ['songchart impact --verify']);
$impactRecommendationStatus = $impactResolution === []
    ? 'not_applicable'
    : ($focusedExecutionEntrypoints === [] ? 'blocked' : 'ready');

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
$state['control_plane']['impact_aware_verification'] = [
    'status' => $impactRecommendationStatus,
    'mode' => $impactResolution['mode'] ?? 'no-change-surface',
    'changed_path_count' => is_array($impactResolution['changed_paths'] ?? null) ? count($impactResolution['changed_paths']) : 0,
    'matched_impact_rules' => array_values(array_unique($matchedRuleNames)),
    'impacted_authorities' => array_values(array_unique($impactedAuthorityNames)),
    'resolved_focused_checks' => $resolvedChecks,
    'recommended_entrypoints' => $impactResolution === [] ? [] : $focusedExecutionEntrypoints,
    'resolver_owner' => $impactResolverPath,
    'impact_map_authority' => $impactMapPath,
    'execution_owner' => $impactRunnerPath,
    'deduplication_rule' => 'Do not execute resolved child checks independently. The registered impact verification owner collapses overlapping checks under their semantic owner before execution.',
    'mutation_allowed' => false,
    'human_gate_required_for_writes' => true,
];
$state['control_plane']['operation_bundles'] = [
    'status' => in_array('blocked', array_column($operationBundles, 'status'), true) ? 'blocked' : 'ready',
    'source' => $planPath.' + '.$surfacePath,
    'bundles' => $operationBundles,
    'autonomous_execution_allowed' => false,
];

if (
    $guidanceStatus === 'blocked'
    || $state['control_plane']['operation_bundles']['status'] === 'blocked'
    || $impactRecommendationStatus === 'blocked'
) {
    $state['control_plane']['status'] = 'blocked';
}

fwrite(STDOUT, json_encode(
    $state,
    JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR,
).PHP_EOL);
