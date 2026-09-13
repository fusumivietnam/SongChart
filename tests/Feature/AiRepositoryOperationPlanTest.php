<?php

declare(strict_types=1);

use Symfony\Component\Process\Process;

it('exposes a projection-only repository operation plan with explicit human write gates', function (): void {
    $process = new Process(['bash', base_path('scripts/ai-status.sh'), '--json'], base_path());
    $process->setTimeout(30);
    $process->run();

    expect($process->isSuccessful())->toBeTrue($process->getErrorOutput());

    $state = json_decode($process->getOutput(), true, flags: JSON_THROW_ON_ERROR);
    $plan = $state['control_plane']['repository_operation_plan'];
    $resume = $state['control_plane']['resume_workflow'];

    expect(in_array($plan['status'], ['ready_for_human_review', 'blocked'], true))->toBeTrue()
        ->and($plan['source'])->toBe('control_plane.operation_bundles + control_plane.resume_workflow')
        ->and($plan['stage'])->toBe('23.0')
        ->and($plan['active_tranche'])->toBe('23.0D')
        ->and($plan['branch'])->toBe($resume['branch'])
        ->and($plan['head_sha'])->toBe($resume['head_sha'])
        ->and($plan['human_approval_required_for_writes'])->toBeTrue()
        ->and($plan['autonomous_execution_allowed'])->toBeFalse()
        ->and($plan['mutation_allowed'])->toBeFalse()
        ->and($plan['secrets_included'])->toBeFalse()
        ->and($plan['missing_operation_bundles'])->toBe([])
        ->and(array_column($plan['read_only_phases'], 'id'))->toBe(['orient', 'implement', 'verify', 'close'])
        ->and(array_column($plan['write_capable_actions'], 'id'))->toBe([
            'source_change',
            'commit_push',
            'pr_promotion',
            'merge',
            'release',
            'production_mutation',
        ]);

    foreach ($plan['read_only_phases'] as $phase) {
        expect($phase['mutation_allowed'])->toBeFalse();
    }

    foreach ($plan['write_capable_actions'] as $action) {
        expect($action['requires_human_approval'])->toBeTrue()
            ->and($action['execution_allowed'])->toBeFalse()
            ->and($action['auto_execute'])->toBeFalse()
            ->and($action['commands'])->toBe([]);
    }
});

it('does not leak secrets through the repository operation plan', function (): void {
    $secret = 'songchart-stage-23-operation-plan-secret';
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
