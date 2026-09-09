<?php

declare(strict_types=1);

use Symfony\Component\Process\Process;

it('exposes the active repository work lease through the AI status JSON surface', function (): void {
    $process = new Process(['bash', base_path('scripts/ai-status.sh'), '--json'], base_path());
    $process->setTimeout(30);
    $process->run();

    expect($process->isSuccessful())->toBeTrue($process->getErrorOutput());

    $state = json_decode($process->getOutput(), true, flags: JSON_THROW_ON_ERROR);

    expect($state['schema_version'])->toBe(2)
        ->and($state['current_stage']['id'])->toBe('22.3')
        ->and($state['accepted_through'])->toBe('22.2')
        ->and($state['live_work_lease']['source'])->toBe('git-and-github-runtime')
        ->and($state['control_plane']['orientation']['evidence']['stage_plan'])->toBe('docs/project/engineering/stage-plan.json')
        ->and($state['control_plane']['runtime']['status'])->toBe('not_evaluated')
        ->and($state['control_plane']['handoff']['live_pr_resolution_required'])->toBeTrue()
        ->and($state['control_plane']['handoff']['live_workflow_resolution_required'])->toBeTrue()
        ->and($state['control_plane']['handoff']['secrets_included'])->toBeFalse();
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

it('does not expose environment secrets in machine-readable handoff state', function (): void {
    $secret = 'songchart-stage-22-3-secret-sentinel';
    $process = new Process([PHP_BINARY, base_path('scripts/project-state.php'), '--json'], base_path(), [
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
