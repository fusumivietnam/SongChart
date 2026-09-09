<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$args = array_slice($argv, 1);
$jsonOnly = in_array('--json', $args, true);
$writeSource = in_array('--write-source', $args, true);
$writeRuntime = in_array('--write', $args, true);

/** @return array<string,mixed> */
function readJsonFile(string $path): array
{
    if (is_file($path) === false) {
        return [];
    }

    $decoded = json_decode((string) file_get_contents($path), true, flags: JSON_THROW_ON_ERROR);

    return is_array($decoded) ? $decoded : [];
}

function normalizedHash(string $path): ?string
{
    if (is_file($path) === false) {
        return null;
    }

    $content = str_replace(["\r\n", "\r"], "\n", (string) file_get_contents($path));

    return hash('sha256', $content);
}

/** @return array<string,mixed> */
function liveWorkLease(string $root): array
{
    $run = static function (string $command) use ($root): string {
        $output = shell_exec('cd '.escapeshellarg($root).' && '.$command.' 2>/dev/null');

        return trim((string) $output);
    };

    $branch = $run('git branch --show-current');
    $head = $run('git rev-parse HEAD');
    $dirty = $run('git status --porcelain --untracked-files=all') !== '';
    $prNumber = null;
    $githubRef = (string) (getenv('GITHUB_REF') ?: '');
    if (preg_match('~^refs/pull/(\d+)/~', $githubRef, $match) === 1) {
        $prNumber = (int) $match[1];
    }

    return [
        'source' => 'git-and-github-runtime',
        'branch' => $branch !== '' ? $branch : null,
        'head_sha' => $head !== '' ? $head : null,
        'dirty' => $dirty,
        'pr_number' => $prNumber,
        'base_ref' => getenv('GITHUB_BASE_REF') ?: null,
        'head_ref' => getenv('GITHUB_HEAD_REF') ?: null,
        'resume_rule' => 'Before writing, resolve the live PR for this branch/semantic owner. Resume an existing active work lease instead of opening duplicate work.',
    ];
}

/**
 * @param array<string,mixed> $plan
 * @return list<string>
 */
function activeGoals(array $plan): array
{
    $activeTranche = (string) ($plan['active_tranche'] ?? '');
    foreach (($plan['stage_progress'] ?? []) as $item) {
        if (is_array($item) === false || (string) ($item['id'] ?? '') !== $activeTranche) {
            continue;
        }

        return array_values(array_filter(
            $item['goals'] ?? [],
            static fn (mixed $goal): bool => is_string($goal) && $goal !== '',
        ));
    }

    return [];
}

/**
 * @param array<string,mixed> $plan
 * @return array<string,mixed>
 */
function projectContextSummary(string $root, array $plan): array
{
    $path = $root.'/docs/project/generated/project-context.json';
    $context = readJsonFile($path);
    if ($context === []) {
        return [
            'status' => 'blocked',
            'source' => 'docs/project/generated/project-context.json',
            'reason' => 'generated project context is missing or invalid',
        ];
    }

    $currentStage = (string) ($plan['current_stage']['id'] ?? '');
    $contextStage = (string) ($context['candidate']['stage'] ?? '');
    $status = $currentStage !== '' && $contextStage === $currentStage ? 'ready' : 'stale';

    return [
        'status' => $status,
        'source' => 'docs/project/generated/project-context.json',
        'stage' => $contextStage !== '' ? $contextStage : null,
        'expected_stage' => $currentStage !== '' ? $currentStage : null,
        'reason' => $status === 'stale' ? 'generated project context does not match the authored current stage; run the governed PREPARE flow' : null,
        'source_fingerprint' => $context['source_fingerprint'] ?? null,
        'runtime_authority' => $context['runtime_authority'] ?? null,
        'command_surface' => $context['command_surface'] ?? null,
        'project_intelligence' => $context['project_intelligence'] ?? null,
    ];
}

/** @return array<string,array<string,mixed>> */
function controlPlaneCapabilities(): array
{
    return [
        'project_state' => [
            'owner' => 'scripts/project-state.php',
            'command' => './songchart ai status --json',
            'side_effects' => false,
            'runtime_probe' => false,
        ],
        'project_intelligence' => [
            'owner' => 'scripts/project-intelligence.php',
            'command' => './songchart artisan project:intelligence --json',
            'side_effects' => false,
            'runtime_probe' => false,
        ],
        'runtime_readiness' => [
            'owner' => 'songchart:doctor',
            'command' => './songchart artisan songchart:doctor --strict',
            'side_effects' => false,
            'runtime_probe' => true,
            'status' => 'not_evaluated',
        ],
        'development_database' => [
            'owner' => 'development:database-status',
            'command' => './songchart dev db status --json',
            'side_effects' => false,
            'runtime_probe' => true,
            'environment' => 'local',
            'status' => 'not_evaluated',
        ],
        'development_storage' => [
            'owner' => 'development:storage-status',
            'command' => './songchart artisan development:storage-status --json',
            'side_effects' => false,
            'runtime_probe' => true,
            'environment' => 'local',
            'status' => 'not_evaluated',
        ],
        'recording_data_trace' => [
            'owner' => 'songchart:data:trace',
            'command' => './songchart artisan songchart:data:trace <recording> --json',
            'side_effects' => false,
            'runtime_probe' => true,
            'requires' => ['recording'],
            'status' => 'not_evaluated',
        ],
        'deep_diagnostics' => [
            'owner' => 'scripts/ai-doctor.sh',
            'command' => './songchart ai doctor',
            'side_effects' => false,
            'runtime_probe' => true,
            'secret_redacted' => true,
            'status' => 'not_evaluated',
        ],
        'impact_resolution' => [
            'owner' => 'scripts/resolve-repository-impact.php',
            'command' => './songchart impact --diff',
            'side_effects' => false,
            'runtime_probe' => false,
        ],
        'impact_verification' => [
            'owner' => 'scripts/run-impact-verification.sh',
            'command' => './songchart impact --verify',
            'side_effects' => false,
            'runtime_probe' => true,
        ],
        'candidate_verification' => [
            'owner' => './songchart candidate',
            'command' => './songchart candidate',
            'side_effects' => false,
            'runtime_probe' => true,
            'requires_clean_tree' => true,
        ],
        'canonical_verification' => [
            'owner' => './songchart verify',
            'command' => './songchart verify',
            'side_effects' => false,
            'runtime_probe' => true,
        ],
    ];
}

/**
 * @param array<string,mixed> $plan
 * @param array<string,mixed> $lease
 * @param array<string,mixed> $context
 * @return array<string,mixed>
 */
function controlPlaneState(array $plan, array $lease, array $context): array
{
    $stage = is_array($plan['current_stage'] ?? null) ? $plan['current_stage'] : [];
    $taskContract = (string) ($stage['task_contract'] ?? '');
    $taskExists = $taskContract !== '' && is_file(dirname(__DIR__).'/'.$taskContract);
    $contextReady = ($context['status'] ?? 'blocked') === 'ready';
    $orientationStatus = $taskExists && $contextReady ? 'ready' : 'blocked';
    $handoffStatus = ($lease['dirty'] ?? true) ? 'degraded' : 'ready';

    return [
        'schema_version' => 1,
        'status' => $orientationStatus === 'ready' ? $handoffStatus : 'blocked',
        'orientation' => [
            'status' => $orientationStatus,
            'evidence' => [
                'stage_plan' => 'docs/project/engineering/stage-plan.json',
                'task_contract' => $taskContract !== '' ? $taskContract : null,
                'task_contract_present' => $taskExists,
                'project_context_status' => $context['status'] ?? 'blocked',
                'source' => 'docs/project/engineering/stage-plan.json + docs/project/generated/project-context.json',
            ],
        ],
        'runtime' => [
            'status' => 'not_evaluated',
            'reason' => 'Repository orientation does not execute environment-specific runtime probes.',
            'probe' => './songchart artisan songchart:doctor --strict',
            'deep_diagnostics' => './songchart ai doctor',
            'source' => 'songchart:doctor + scripts/ai-doctor.sh',
        ],
        'handoff' => [
            'status' => $handoffStatus,
            'branch' => $lease['branch'] ?? null,
            'head_sha' => $lease['head_sha'] ?? null,
            'working_tree_clean' => ($lease['dirty'] ?? true) === false,
            'live_pr_resolution_required' => true,
            'live_workflow_resolution_required' => true,
            'secrets_included' => false,
            'resume_rule' => $lease['resume_rule'] ?? null,
            'source' => 'git-and-github-runtime',
        ],
        'next_actions' => [
            'source' => 'docs/project/engineering/stage-plan.json',
            'goals' => activeGoals($plan),
        ],
        'verification_guidance' => [
            'source' => 'canonical SongChart verification command surface',
            'impact' => './songchart impact --diff',
            'focused' => './songchart impact --verify',
            'candidate' => './songchart candidate',
            'canonical' => './songchart verify',
            'promotion_rule' => 'Resolve exact-head GitHub Auto Closure before claiming major-stage acceptance or promotion readiness.',
        ],
        'capabilities' => controlPlaneCapabilities(),
        'boundaries' => [
            'source' => 'docs/project/engineering/mcp-contract.json + repository authority rules',
            'repository_authority' => 'Git repository plus authored machine authorities',
            'live_work_lease_authority' => 'Git branch plus live GitHub pull request',
            'mcp_role' => 'future thin adapter only',
            'ai_memory_authority' => false,
            'autonomous_repository_writes' => false,
            'autonomous_production_writes' => false,
        ],
    ];
}

/** @param array<string,mixed> $state */
function stateMarkdown(array $state): string
{
    $stage = is_array($state['current_stage'] ?? null) ? $state['current_stage'] : [];
    $next = $state['next_tranche'] ?? 'unknown';
    $nextLabel = is_array($next) ? (($next['id'] ?? 'unknown').' — '.($next['title'] ?? 'unknown')) : (string) $next;
    $lines = [
        '# Generated Development State',
        '',
        '> Generated from repository machine authorities. Do not edit this file manually.',
        '',
        '## Current stage',
        '',
        '- Stage `'.($stage['id'] ?? 'unknown').' — '.($stage['title'] ?? 'unknown').'`',
        '- Status: `'.($stage['status'] ?? 'unknown').'`',
        '- Accepted through: `'.($state['accepted_through'] ?? 'unknown').'`',
        '- Task contract: `'.($stage['task_contract'] ?? 'unknown').'`',
        '',
        '## Stage progress',
        '',
    ];

    foreach (($state['stage_progress'] ?? []) as $item) {
        if (is_array($item) === false) {
            continue;
        }
        $lines[] = '- `'.($item['id'] ?? '?').'` — `'.strtoupper((string) ($item['status'] ?? 'unknown')).'` — '.($item['title'] ?? '');
    }

    $lines = array_merge($lines, [
        '',
        '## Next bounded tranche',
        '',
        '- `'.$nextLabel.'`',
        '',
        '## Live work lease',
        '',
        'Branch, PR number, exact head SHA and verification lifecycle are volatile runtime facts. Resolve them from Git/GitHub at session start with `./songchart ai status --json`; never copy them into authored progress prose.',
        '',
        '## Device / AI handoff',
        '',
        'Any device or AI client must orient from repository machine state plus the live GitHub PR before writing. If another device has advanced the PR head, refresh and resume that exact work lease instead of continuing stale local state.',
        '',
    ]);

    return implode(PHP_EOL, $lines);
}

try {
    $planPath = $root.'/docs/project/engineering/stage-plan.json';
    $plan = readJsonFile($planPath);
    if ($plan === []) {
        throw new RuntimeException('Missing or invalid stage-plan.json.');
    }

    $sources = [
        'docs/project/engineering/stage-plan.json',
        'docs/project/engineering/project-knowledge.json',
        'docs/project/engineering/consolidation-plan.json',
        'candidate-verification.json',
    ];
    $hashes = [];
    foreach ($sources as $relative) {
        $hash = normalizedHash($root.'/'.$relative);
        if ($hash !== null) {
            $hashes[$relative] = $hash;
        }
    }
    ksort($hashes);

    $state = [
        'schema_version' => 2,
        'generated_from_repository' => true,
        'source_fingerprint' => hash('sha256', json_encode($hashes, JSON_THROW_ON_ERROR)),
        'current_stage' => $plan['current_stage'] ?? null,
        'accepted_through' => $plan['accepted_through'] ?? null,
        'stage_progress' => $plan['stage_progress'] ?? [],
        'next_tranche' => $plan['next_tranche'] ?? null,
        'work_lease_policy' => $plan['work_lease'] ?? null,
        'source_hashes' => $hashes,
    ];

    if ($writeSource === false) {
        $lease = liveWorkLease($root);
        $context = projectContextSummary($root, $plan);
        $state['live_work_lease'] = $lease;
        $state['project_context'] = $context;
        $state['control_plane'] = controlPlaneState($plan, $lease, $context);
    }

    if ($writeSource) {
        $directory = $root.'/docs/project/generated';
        if (is_dir($directory) === false) {
            mkdir($directory, 0777, true);
        }
        file_put_contents($directory.'/development-state.json', json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR).PHP_EOL);
        file_put_contents($directory.'/DEVELOPMENT_STATE.md', stateMarkdown($state));
    } elseif ($writeRuntime) {
        $directory = $root.'/storage/project-state';
        if (is_dir($directory) === false) {
            mkdir($directory, 0777, true);
        }
        file_put_contents($directory.'/development-state.json', json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR).PHP_EOL);
    }

    if ($jsonOnly) {
        fwrite(STDOUT, json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR).PHP_EOL);
        exit(0);
    }

    $stage = is_array($state['current_stage'] ?? null) ? $state['current_stage'] : [];
    $next = $state['next_tranche'] ?? 'unknown';
    $nextLabel = is_array($next) ? (($next['id'] ?? 'unknown').' — '.($next['title'] ?? 'unknown')) : (string) $next;
    fwrite(STDOUT, 'SongChart Derived Project State'.PHP_EOL);
    fwrite(STDOUT, 'Stage: '.($stage['id'] ?? 'unknown').' — '.($stage['status'] ?? 'unknown').PHP_EOL);
    fwrite(STDOUT, 'Accepted through: '.($state['accepted_through'] ?? 'unknown').PHP_EOL);
    fwrite(STDOUT, 'Next tranche: '.$nextLabel.PHP_EOL);
    if (isset($state['live_work_lease']) && is_array($state['live_work_lease'])) {
        $lease = $state['live_work_lease'];
        fwrite(STDOUT, 'Work lease: branch='.($lease['branch'] ?? 'unknown').' head='.substr((string) ($lease['head_sha'] ?? 'unknown'), 0, 12).' pr='.($lease['pr_number'] ?? 'n/a').' dirty='.(($lease['dirty'] ?? true) ? 'yes' : 'no').PHP_EOL);
    }
    if (isset($state['control_plane']) && is_array($state['control_plane'])) {
        fwrite(STDOUT, 'Control plane: '.($state['control_plane']['status'] ?? 'unknown').PHP_EOL);
    }
} catch (Throwable $exception) {
    fwrite(STDERR, 'Unable to build derived project state: '.$exception->getMessage().PHP_EOL);
    exit(1);
}
