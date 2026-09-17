<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$jsonOnly = in_array('--json', array_slice($argv, 1), true);

/** @return array<string, mixed> */
function readJson(string $path): array
{
    if (! is_file($path)) {
        return [];
    }

    $decoded = json_decode((string) file_get_contents($path), true, flags: JSON_THROW_ON_ERROR);

    return is_array($decoded) ? $decoded : [];
}

function runGit(string $root, string $command): string
{
    $output = shell_exec('cd '.escapeshellarg($root).' && git '.$command.' 2>/dev/null');

    return trim((string) $output);
}

/** @param array<string, mixed> $plan */
function activeTranche(array $plan): ?array
{
    $activeId = $plan['active_tranche'] ?? null;
    if (! is_string($activeId) || $activeId === '') {
        return null;
    }

    foreach (($plan['stage_progress'] ?? []) as $item) {
        if (is_array($item) && ($item['id'] ?? null) === $activeId) {
            return $item;
        }
    }

    return ['id' => $activeId, 'status' => 'unknown'];
}

/** @param array<string, mixed> $plan */
function authoredExecutionState(array $plan): string
{
    $stage = is_array($plan['current_stage'] ?? null) ? $plan['current_stage'] : [];
    $stageStatus = (string) ($stage['status'] ?? 'unknown');
    $tranche = activeTranche($plan);

    if ($stageStatus === 'accepted' && $tranche === null) {
        return 'ACCEPTED';
    }

    if ($tranche !== null) {
        return match ((string) ($tranche['status'] ?? 'unknown')) {
            'planned' => 'PLANNED',
            'active' => 'IMPLEMENTING',
            'accepted' => 'ACCEPTED',
            default => 'IMPLEMENTING',
        };
    }

    return strtoupper($stageStatus);
}

/** @return array<string, mixed> */
function runtimeVerification(string $head): array
{
    $status = trim((string) (getenv('SONGCHART_VERIFICATION_STATUS') ?: 'unresolved'));
    $verifiedSha = trim((string) (getenv('SONGCHART_VERIFICATION_SHA') ?: ''));
    $runId = trim((string) (getenv('SONGCHART_VERIFICATION_RUN_ID') ?: ''));
    $matchesHead = $verifiedSha !== '' && $head !== '' && hash_equals($head, $verifiedSha);

    return [
        'status' => $status,
        'verified_sha' => $verifiedSha !== '' ? $verifiedSha : null,
        'run_id' => $runId !== '' ? $runId : null,
        'matches_current_head' => $matchesHead,
        'source' => 'git-and-github-runtime',
    ];
}

try {
    $plan = readJson($root.'/docs/project/engineering/stage-plan.json');
    $registry = readJson($root.'/docs/project/engineering/roadmap-registry.json');
    if ($plan === []) {
        throw new RuntimeException('Missing or invalid stage-plan.json.');
    }
    if ($registry === []) {
        throw new RuntimeException('Missing or invalid roadmap-registry.json.');
    }

    $branch = runGit($root, 'branch --show-current');
    $head = runGit($root, 'rev-parse HEAD');
    $dirty = runGit($root, 'status --porcelain --untracked-files=all') !== '';
    $verification = runtimeVerification($head);
    $authoredState = authoredExecutionState($plan);
    $derivedState = $authoredState;

    if (($verification['status'] ?? null) === 'passed' && ($verification['matches_current_head'] ?? false) === true) {
        $derivedState = $authoredState === 'ACCEPTED' ? 'ACCEPTED' : 'CI_VERIFIED';
    } elseif ($authoredState === 'IMPLEMENTING' && $dirty === false) {
        $derivedState = 'IMPLEMENTING';
    }

    $stage = is_array($plan['current_stage'] ?? null) ? $plan['current_stage'] : [];
    $tranche = activeTranche($plan);
    $nextTransition = match ($derivedState) {
        'CI_VERIFIED' => 'acceptance_candidate',
        'ACCEPTED' => 'activate_next_bounded_work_only_with_explicit_authority',
        'PLANNED' => 'activate_when_gate_is_explicitly_accepted',
        default => 'continue_bounded_implementation_and_focused_verification',
    };

    $themes = [];
    foreach (($registry['themes'] ?? []) as $theme) {
        if (! is_array($theme)) {
            continue;
        }
        $themes[] = [
            'id' => $theme['id'] ?? null,
            'title' => $theme['title'] ?? null,
            'state' => $theme['state'] ?? 'unknown',
            'activation_gate' => $theme['activation_gate'] ?? null,
            'depends_on' => array_values(array_filter($theme['depends_on'] ?? [], 'is_string')),
        ];
    }

    $state = [
        'schema_version' => 1,
        'authority' => [
            'strategic_roadmap' => 'docs/project/docs/ROADMAP.md',
            'candidate_registry' => 'docs/project/engineering/roadmap-registry.json',
            'execution_plan' => 'docs/project/engineering/stage-plan.json',
            'runtime' => 'git-and-github-runtime',
        ],
        'current' => [
            'stage' => [
                'id' => $stage['id'] ?? null,
                'title' => $stage['title'] ?? null,
                'authored_status' => $stage['status'] ?? null,
                'accepted_through' => $plan['accepted_through'] ?? null,
            ],
            'active_tranche' => $tranche,
            'authored_delivery_state' => $authoredState,
            'derived_delivery_state' => $derivedState,
            'next_transition' => $nextTransition,
        ],
        'worktree' => [
            'branch' => $branch !== '' ? $branch : null,
            'head_sha' => $head !== '' ? $head : null,
            'clean' => ! $dirty,
        ],
        'verification' => $verification,
        'roadmap_candidates' => $themes,
        'rules' => [
            'runtime_facts_are_not_authored_progress' => true,
            'registry_does_not_auto_activate_work' => true,
            'acceptance_requires_explicit_authority_transition' => true,
            'production_promotion_remains_human_controlled' => true,
        ],
    ];

    if ($jsonOnly) {
        echo json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR).PHP_EOL;
        exit(0);
    }

    $stageLabel = (string) (($stage['id'] ?? 'unknown').' — '.($stage['title'] ?? 'unknown'));
    $trancheLabel = $tranche === null
        ? 'none'
        : (string) (($tranche['id'] ?? 'unknown').' — '.($tranche['title'] ?? 'unknown'));

    echo "SongChart Roadmap State\n";
    echo "Stage: {$stageLabel}\n";
    echo 'Authored stage status: '.($stage['status'] ?? 'unknown')."\n";
    echo 'Accepted through: '.($plan['accepted_through'] ?? 'unknown')."\n";
    echo "Active tranche: {$trancheLabel}\n";
    echo "Delivery state: {$authoredState} -> {$derivedState}\n";
    echo "Next transition: {$nextTransition}\n";
    echo 'Verification: '.($verification['status'] ?? 'unresolved')."\n";
    echo 'Roadmap candidates: '.count($themes)."\n";
} catch (Throwable $exception) {
    fwrite(STDERR, 'SongChart roadmap state: '.$exception->getMessage().PHP_EOL);
    exit(1);
}
