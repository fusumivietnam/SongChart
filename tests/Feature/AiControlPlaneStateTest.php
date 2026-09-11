<?php

declare(strict_types=1);

use Symfony\Component\Process\Process;

it('exposes bounded repository handoff, guidance, operation bundles, impact-aware verification and resume workflow through the AI status JSON surface', function (): void {
    $process = new Process(['bash', base_path('scripts/ai-status.sh'), '--json'], base_path());
    $process->setTimeout(30);
    $process->run();

    expect($process->isSuccessful())->toBeTrue($process->getErrorOutput());

    $state = json_decode($process->getOutput(), true, flags: JSON_THROW_ON_ERROR);
    $controlPlane = $state['control_plane'];
    $handoff = $controlPlane['handoff'];
    $changeSurface = $handoff['local_change_surface'];
    $nextActions = $controlPlane['next_actions'];
    $verification = $controlPlane['verification_guidance'];
    $impactAware = $controlPlane['impact_aware_verification'];
    $operations = $controlPlane['operation_bundles'];
    $bundles = $operations['bundles'];
    $resume = $controlPlane['resume_workflow'];

    expect($state['schema_version'])->toBe(2)
        ->and($state['current_stage']['id'])->toBe('22.4')
        ->and($state['accepted_through'])->toBe('22.3')
        ->and($state['live_work_lease']['source'])->toBe('git-and-github-runtime')
        ->and($controlPlane['orientation']['evidence']['stage_plan'])->toBe('docs/project/engineering/stage-plan.json')
        ->and($controlPlane['runtime']['status'])->toBe('not_evaluated')
        ->and($handoff['stage'])->toBe('22.4')
        ->and($handoff['active_tranche'])->toBe('22.4D')
        ->and($handoff['task_contract'])->toBe('docs/foundation/STAGE_22_4_TASK_CONTRACT.md')
        ->and($handoff['head_sha'])->toBe($state['live_work_lease']['head_sha'])
        ->and($handoff['committed_pr_change_surface']['status'])->toBe('requires_live_pr_resolution')
        ->and($handoff['verification']['status'])->toBe('requires_live_workflow_resolution')
        ->and($handoff['live_pr_resolution_required'])->toBeTrue()
        ->and($handoff['live_workflow_resolution_required'])->toBeTrue()
        ->and($handoff['secrets_included'])->toBeFalse()
        ->and($changeSurface['path_count'])->toBeInt()
        ->and($changeSurface['paths'])->toBeArray()
        ->and($changeSurface['path_limit'])->toBe(100)
        ->and($changeSurface['truncated'])->toBeBool()
        ->and(count($changeSurface['paths']))->toBeLessThanOrEqual(100)
        ->and($nextActions['status'])->toBe('ready')
        ->and($nextActions['active_tranche'])->toBe('22.4D')
        ->and($nextActions['human_gate_required_for_writes'])->toBeTrue()
        ->and($nextActions['actions'])->toBeArray()->not->toBeEmpty()
        ->and($verification['status'])->toBe('ready')
        ->and($verification['impact']['owner'])->toBe('scripts/resolve-repository-impact.php')
        ->and($verification['impact']['commands'])->toContain('./songchart impact --diff')
        ->and($verification['focused_entrypoints'])->toContain('songchart impact --verify')
        ->and($verification['candidate_closure_entrypoints'])->toContain('songchart candidate')
        ->and($verification['canonical_closure_entrypoints'])->toContain('songchart verify')
        ->and($verification['writes_remain_human_gated'])->toBeTrue()
        ->and(in_array($impactAware['status'], ['ready', 'not_applicable'], true))->toBeTrue()
        ->and($impactAware['resolver_owner'])->toBe('scripts/resolve-repository-impact.php')
        ->and($impactAware['impact_map_authority'])->toBe('docs/project/stack/impact-test-map.json')
        ->and($impactAware['execution_owner'])->toBe('scripts/run-impact-verification.sh')
        ->and($impactAware['mutation_allowed'])->toBeFalse()
        ->and($impactAware['human_gate_required_for_writes'])->toBeTrue()
        ->and($operations['status'])->toBe('ready')
        ->and($operations['autonomous_execution_allowed'])->toBeFalse()
        ->and(array_keys($bundles))->toBe(['orient', 'implement', 'verify', 'close'])
        ->and($bundles['orient']['commands'])->toContain('songchart ai status')
        ->and($bundles['implement']['stage'])->toBe('22.4')
        ->and($bundles['implement']['active_tranche'])->toBe('22.4D')
        ->and($bundles['implement']['commands'])->toContain('songchart impact --diff')
        ->and($bundles['verify']['commands'])->toContain('songchart impact --verify')
        ->and($bundles['close']['candidate_commands'])->toContain('songchart candidate')
        ->and($bundles['close']['canonical_commands'])->toContain('songchart verify')
        ->and(in_array($resume['status'], ['requires_live_resolution', 'degraded', 'blocked'], true))->toBeTrue()
        ->and($resume['branch'])->toBe($handoff['branch'])
        ->and($resume['head_sha'])->toBe($handoff['head_sha'])
        ->and($resume['stage'])->toBe('22.4')
        ->and($resume['active_tranche'])->toBe('22.4D')
        ->and($resume['task_contract'])->toBe('docs/foundation/STAGE_22_4_TASK_CONTRACT.md')
        ->and($resume['working_tree_clean'])->toBe($handoff['working_tree_clean'])
        ->and($resume['live_pr_resolution_required'])->toBeTrue()
        ->and($resume['live_workflow_resolution_required'])->toBeTrue()
        ->and($resume['chat_memory_authority'])->toBeFalse()
        ->and($resume['volatile_github_state_persisted'])->toBeFalse()
        ->and($resume['mutation_allowed'])->toBeFalse()
        ->and($resume['human_gate_required_for_writes'])->toBeTrue()
        ->and($resume['steps'])->toBeArray()->toHaveCount(4);

    if ($handoff['branch'] === null) {
        expect($resume['status'])->toBe('blocked');
    } elseif ($handoff['working_tree_clean']) {
        expect($resume['status'])->toBe('requires_live_resolution');
    } else {
        expect($resume['status'])->toBe('degraded');
    }

    expect(array_column($resume['steps'], 'id'))->toBe([
        'orient-local-authority',
        'resolve-live-pr',
        'resolve-exact-head-workflow',
        'resolve-change-impact',
    ]);

    foreach ($resume['steps'] as $step) {
        expect($step['mutation_allowed'])->toBeFalse();
    }

    if ($impactAware['status'] === 'ready') {
        expect($impactAware['mode'])->toBe('actual-diff')
            ->and($impactAware['changed_path_count'])->toBeGreaterThan(0)
            ->and($impactAware['resolved_focused_checks'])->toBeArray()->not->toBeEmpty()
            ->and($impactAware['recommended_entrypoints'])->toBe(['songchart impact --verify']);
    } else {
        expect($impactAware['mode'])->toBe('no-change-surface')
            ->and($impactAware['changed_path_count'])->toBe(0)
            ->and($impactAware['resolved_focused_checks'])->toBe([])
            ->and($impactAware['recommended_entrypoints'])->toBe([]);
    }

    foreach ($nextActions['actions'] as $action) {
        expect($action['mutation_allowed'])->toBeFalse();
    }

    foreach ($bundles as $bundle) {
        expect($bundle['mutation_allowed'])->toBeFalse()
            ->and($bundle['human_gate_required_for_writes'])->toBeTrue()
            ->and($bundle['source'])->toBeString()->not->toBeEmpty();
    }
});

it('delegates verification-set resolution and execution deduplication to existing owners', function (): void {
    $process = new Process(['bash', base_path('scripts/ai-status.sh'), '--json'], base_path());
    $process->setTimeout(30);
    $process->run();

    expect($process->isSuccessful())->toBeTrue($process->getErrorOutput());

    $state = json_decode($process->getOutput(), true, flags: JSON_THROW_ON_ERROR);
    $impactAware = $state['control_plane']['impact_aware_verification'];

    expect($impactAware['resolver_owner'])->toBe('scripts/resolve-repository-impact.php')
        ->and($impactAware['execution_owner'])->toBe('scripts/run-impact-verification.sh')
        ->and(in_array($impactAware['status'], ['ready', 'not_applicable'], true))->toBeTrue()
        ->and($impactAware['deduplication_rule'])->toContain('semantic owner')
        ->and($impactAware)->not->toHaveKey('execution_plan')
        ->and($impactAware)->not->toHaveKey('auto_execute');

    if ($impactAware['status'] === 'ready') {
        expect($impactAware['recommended_entrypoints'])->toBe(['songchart impact --verify']);
    } else {
        expect($impactAware['recommended_entrypoints'])->toBe([]);
    }
});

it('keeps resume continuity bounded to repository and live GitHub authority', function (): void {
    $process = new Process(['bash', base_path('scripts/ai-status.sh'), '--json'], base_path());
    $process->setTimeout(30);
    $process->run();

    expect($process->isSuccessful())->toBeTrue($process->getErrorOutput());

    $state = json_decode($process->getOutput(), true, flags: JSON_THROW_ON_ERROR);
    $resume = $state['control_plane']['resume_workflow'];
    $handoff = $state['control_plane']['handoff'];

    expect($resume['source'])->toContain('scripts/ai-handoff-status.php')
        ->and($resume['source'])->toContain('docs/project/engineering/stage-plan.json')
        ->and($resume['source'])->toContain('docs/project/engineering/verification-command-surface.json')
        ->and($resume['head_sha'])->toBe($handoff['head_sha'])
        ->and($resume['branch'])->toBe($handoff['branch'])
        ->and($resume['chat_memory_authority'])->toBeFalse()
        ->and($resume['volatile_github_state_persisted'])->toBeFalse()
        ->and($resume)->not->toHaveKey('pull_request_number')
        ->and($resume)->not->toHaveKey('workflow_run_id')
        ->and($resume)->not->toHaveKey('auto_execute')
        ->and($resume)->not->toHaveKey('write_plan');
});

it('composes existing owners instead of duplicating diagnostic and verification capabilities', function (): void {
    $process = new Process([PHP_BINARY, base_path('scripts/project-state.php'), '--json'], base_path());
    $process->setTimeout(30);
    $process->run();

    expect($process->isSuccessful())->toBeTrue($process->getErrorOutput());

    $state = json_decode($process->getOutput(), true, flags: JSON_THROW_ON_ERROR);
    $capabilities = $state['control_plane']['capabilities'];

    expect($capabilities['project_intelligence']['owner'])->toBe('scripts/project-intelligence.php')
        ->and($capabilities['runtime_readiness']['owner'])->toBe('songchart:doctor')
        ->and($capabilities['development_database']['owner'])->toBe('development:database-status')
        ->and($capabilities['development_storage']['owner'])->toBe('development:storage-status')
        ->and($capabilities['recording_data_trace']['owner'])->toBe('songchart:data:trace')
        ->and($capabilities['impact_verification']['owner'])->toBe('scripts/run-impact-verification.sh')
        ->and($capabilities['candidate_verification']['requires_clean_tree'])->toBeTrue()
        ->and($state['control_plane']['boundaries']['ai_memory_authority'])->toBeFalse()
        ->and($state['control_plane']['boundaries']['autonomous_repository_writes'])->toBeFalse()
        ->and($state['control_plane']['boundaries']['autonomous_production_writes'])->toBeFalse();
});

it('does not expose environment secrets in machine-readable handoff, guidance, operation bundles, impact recommendations or resume workflow', function (): void {
    $secret = 'songchart-stage-22-4-secret-sentinel';
    $process = new Process(['bash', base_path('scripts/ai-status.sh'), '--json'], base_path(), [
        'DB_PASSWORD' => $secret,
        'YOUTUBE_API_KEY' => $secret,
    ]);
    $process->setTimeout(30);
    $process->run();

    expect($process->isSuccessful())->toBeTrue($process->getErrorOutput())
        ->and($process->getOutput())->not->toContain($secret)
        ->and($process->getOutput())->not->toContain('DB_PASSWORD')
        ->and($process->getOutput())->not->toContain('YOUTUBE_API_KEY');
});
