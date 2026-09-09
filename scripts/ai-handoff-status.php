<?php

declare(strict_types=1);

$root = dirname(__DIR__);

/**
 * @param  list<string>  $command
 * @return array{output:string,exit_code:int}
 */
function runReadOnlyCommand(array $command, string $workingDirectory): array
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

    if (! is_resource($process)) {
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

/** @return list<string> */
function nullSeparatedPaths(string $output): array
{
    $paths = array_values(array_filter(
        explode("\0", $output),
        static fn (string $path): bool => $path !== '',
    ));
    sort($paths, SORT_STRING);

    return array_values(array_unique($paths));
}

/** @param array<string,mixed> $state */
function activeTranche(array $state): ?string
{
    foreach (($state['stage_progress'] ?? []) as $item) {
        if (! is_array($item) || ($item['status'] ?? null) !== 'active') {
            continue;
        }

        $id = $item['id'] ?? null;

        return is_string($id) && $id !== '' ? $id : null;
    }

    return null;
}

$stateResult = runReadOnlyCommand([PHP_BINARY, $root.'/scripts/project-state.php', '--json'], $root);
if ($stateResult['exit_code'] !== 0) {
    fwrite(STDERR, "Unable to load repository project state.\n");
    exit(1);
}

try {
    $state = json_decode($stateResult['output'], true, flags: JSON_THROW_ON_ERROR);
} catch (Throwable) {
    fwrite(STDERR, "Repository project state is not valid JSON.\n");
    exit(1);
}

if (! is_array($state) || ! is_array($state['control_plane'] ?? null)) {
    fwrite(STDERR, "Repository project state is missing control-plane authority.\n");
    exit(1);
}

$branch = trim(runReadOnlyCommand(['git', 'branch', '--show-current'], $root)['output']);
$head = trim(runReadOnlyCommand(['git', 'rev-parse', 'HEAD'], $root)['output']);
$upstreamResult = runReadOnlyCommand(['git', 'rev-parse', '--abbrev-ref', '--symbolic-full-name', '@{u}'], $root);
$upstream = $upstreamResult['exit_code'] === 0 ? trim($upstreamResult['output']) : '';
$ahead = null;
$behind = null;
if ($upstream !== '') {
    $counts = preg_split('/\s+/', trim(runReadOnlyCommand(['git', 'rev-list', '--left-right', '--count', 'HEAD...'.$upstream], $root)['output']));
    if (is_array($counts) && count($counts) === 2 && ctype_digit($counts[0]) && ctype_digit($counts[1])) {
        $ahead = (int) $counts[0];
        $behind = (int) $counts[1];
    }
}

$trackedPaths = nullSeparatedPaths(runReadOnlyCommand(['git', 'diff', '--name-only', '-z', 'HEAD'], $root)['output']);
$untrackedPaths = nullSeparatedPaths(runReadOnlyCommand(['git', 'ls-files', '--others', '--exclude-standard', '-z'], $root)['output']);
$localPaths = array_values(array_unique([...$trackedPaths, ...$untrackedPaths]));
sort($localPaths, SORT_STRING);
$pathLimit = 100;
$pathCount = count($localPaths);
$boundedPaths = array_slice($localPaths, 0, $pathLimit);
$stage = is_array($state['current_stage'] ?? null) ? $state['current_stage'] : [];
$tranche = activeTranche($state);
$taskContract = $stage['task_contract'] ?? null;

$handoff = [
    'status' => $pathCount === 0 ? 'ready' : 'degraded',
    'source' => 'git-runtime + repository-stage-authority',
    'branch' => $branch !== '' ? $branch : null,
    'head_sha' => $head !== '' ? $head : null,
    'upstream' => $upstream !== '' ? $upstream : null,
    'ahead' => $ahead,
    'behind' => $behind,
    'stage' => is_string($stage['id'] ?? null) ? $stage['id'] : null,
    'active_tranche' => $tranche,
    'task_contract' => is_string($taskContract) && $taskContract !== '' ? $taskContract : null,
    'working_tree_clean' => $pathCount === 0,
    'local_change_surface' => [
        'status' => $pathCount === 0 ? 'clean' : 'changed',
        'source' => 'git diff HEAD + git ls-files --others --exclude-standard',
        'path_count' => $pathCount,
        'paths' => $boundedPaths,
        'path_limit' => $pathLimit,
        'truncated' => $pathCount > $pathLimit,
    ],
    'committed_pr_change_surface' => [
        'status' => 'requires_live_pr_resolution',
        'source' => 'GitHub pull request',
    ],
    'verification' => [
        'status' => 'requires_live_workflow_resolution',
        'source' => 'GitHub Auto Closure on exact PR head',
    ],
    'live_pr_resolution_required' => true,
    'live_workflow_resolution_required' => true,
    'secrets_included' => false,
    'resume_rule' => 'Resolve the live GitHub PR and exact-head workflow before writing. If the PR head differs from this Git head, refresh and resume the live head instead of creating parallel work.',
];

$state['control_plane']['handoff'] = $handoff;
$state['live_work_lease']['branch'] = $handoff['branch'];
$state['live_work_lease']['head_sha'] = $handoff['head_sha'];
$state['live_work_lease']['dirty'] = ! $handoff['working_tree_clean'];

if (($state['control_plane']['orientation']['status'] ?? null) === 'ready') {
    $state['control_plane']['status'] = $handoff['status'];
}

fwrite(STDOUT, json_encode(
    $state,
    JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR,
).PHP_EOL);
