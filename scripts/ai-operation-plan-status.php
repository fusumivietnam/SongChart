<?php

declare(strict_types=1);

$root = dirname(__DIR__);

/**
 * @param  list<string>  $command
 * @return array{output:string,exit_code:int}
 */
function operationPlanRun(array $command, string $workingDirectory): array
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

$result = operationPlanRun([PHP_BINARY, $root.'/scripts/ai-guidance-status.php'], $root);
if ($result['exit_code'] !== 0) {
    fwrite(STDERR, "Unable to load AI guidance state.\n");
    exit(1);
}

try {
    $state = json_decode($result['output'], true, flags: JSON_THROW_ON_ERROR);
} catch (Throwable) {
    fwrite(STDERR, "AI guidance state is not valid JSON.\n");
    exit(1);
}

if (is_array($state) === false || is_array($state['control_plane'] ?? null) === false) {
    fwrite(STDERR, "AI guidance state is missing control-plane authority.\n");
    exit(1);
}

$controlPlane = $state['control_plane'];
$operations = is_array($controlPlane['operation_bundles'] ?? null) ? $controlPlane['operation_bundles'] : [];
$bundles = is_array($operations['bundles'] ?? null) ? $operations['bundles'] : [];
$resume = is_array($controlPlane['resume_workflow'] ?? null) ? $controlPlane['resume_workflow'] : [];

$requiredBundles = ['orient', 'implement', 'verify', 'close'];
$missingBundles = array_values(array_filter(
    $requiredBundles,
    static fn (string $bundle): bool => is_array($bundles[$bundle] ?? null) === false,
));

$blocked = $missingBundles !== []
    || $resume === []
    || ($resume['status'] ?? 'blocked') === 'blocked';

$readOnlyPhases = [];
foreach ($requiredBundles as $bundleId) {
    $bundle = is_array($bundles[$bundleId] ?? null) ? $bundles[$bundleId] : [];
    $commands = [];

    foreach (['commands', 'candidate_commands', 'canonical_commands'] as $commandKey) {
        foreach (($bundle[$commandKey] ?? []) as $command) {
            if (is_string($command) && $command !== '') {
                $commands[] = $command;
            }
        }
    }

    $readOnlyPhases[] = [
        'id' => $bundleId,
        'status' => $bundle['status'] ?? 'blocked',
        'purpose' => $bundle['purpose'] ?? null,
        'commands' => array_values(array_unique($commands)),
        'source' => $bundle['source'] ?? 'control_plane.operation_bundles',
        'mutation_allowed' => false,
    ];
}

$writeCapableActions = [];
foreach ([
    'source_change' => 'Modify tracked repository source or authored authority.',
    'commit_push' => 'Commit or push repository changes.',
    'pr_promotion' => 'Change pull-request readiness, approval or promotion state.',
    'merge' => 'Merge a pull request or change accepted-main state.',
    'release' => 'Create or publish a release.',
    'production_mutation' => 'Change production data, credentials, infrastructure or deployment state.',
] as $id => $purpose) {
    $writeCapableActions[] = [
        'id' => $id,
        'purpose' => $purpose,
        'requires_human_approval' => true,
        'execution_allowed' => false,
        'auto_execute' => false,
        'commands' => [],
    ];
}

$state['control_plane']['repository_operation_plan'] = [
    'status' => $blocked ? 'blocked' : 'ready_for_human_review',
    'source' => 'control_plane.operation_bundles + control_plane.resume_workflow',
    'stage' => $resume['stage'] ?? null,
    'active_tranche' => $resume['active_tranche'] ?? null,
    'branch' => $resume['branch'] ?? null,
    'head_sha' => $resume['head_sha'] ?? null,
    'read_only_phases' => $readOnlyPhases,
    'write_capable_actions' => $writeCapableActions,
    'missing_operation_bundles' => $missingBundles,
    'human_approval_required_for_writes' => true,
    'autonomous_execution_allowed' => false,
    'mutation_allowed' => false,
    'execution_boundary' => 'This projection describes repository operations only. It never invokes source writes, commit/push, PR promotion, merge, release or production mutation.',
    'secrets_included' => false,
];

if ($blocked) {
    $state['control_plane']['status'] = 'blocked';
}

fwrite(STDOUT, json_encode(
    $state,
    JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR,
).PHP_EOL);
