<?php

declare(strict_types=1);

use Symfony\Component\Process\Process;

it('compiles authored roadmap authority and runtime verification without mutating roadmap state', function (): void {
    $process = new Process([PHP_BINARY, base_path('scripts/roadmap-state.php'), '--json'], base_path(), [
        'SONGCHART_VERIFICATION_STATUS' => 'passed',
        'SONGCHART_VERIFICATION_SHA' => trim((string) shell_exec('git -C '.escapeshellarg(base_path()).' rev-parse HEAD')),
        'SONGCHART_VERIFICATION_RUN_ID' => '12345',
    ]);
    $process->setTimeout(30);
    $process->run();

    expect($process->isSuccessful())->toBeTrue($process->getErrorOutput());

    $state = json_decode($process->getOutput(), true, flags: JSON_THROW_ON_ERROR);
    $plan = json_decode(
        (string) file_get_contents(base_path('docs/project/engineering/stage-plan.json')),
        true,
        flags: JSON_THROW_ON_ERROR,
    );

    expect($state['schema_version'])->toBe(1)
        ->and($state['authority']['strategic_roadmap'])->toBe('docs/project/docs/ROADMAP.md')
        ->and($state['authority']['candidate_registry'])->toBe('docs/project/engineering/roadmap-registry.json')
        ->and($state['authority']['execution_plan'])->toBe('docs/project/engineering/stage-plan.json')
        ->and($state['authority']['runtime'])->toBe('git-and-github-runtime')
        ->and($state['current']['stage']['id'])->toBe($plan['current_stage']['id'])
        ->and($state['current']['stage']['authored_status'])->toBe($plan['current_stage']['status'])
        ->and($state['current']['stage']['accepted_through'])->toBe($plan['accepted_through'])
        ->and($state['worktree']['head_sha'])->toBeString()->not->toBeEmpty()
        ->and($state['verification']['status'])->toBe('passed')
        ->and($state['verification']['matches_current_head'])->toBeTrue()
        ->and($state['rules']['runtime_facts_are_not_authored_progress'])->toBeTrue()
        ->and($state['rules']['registry_does_not_auto_activate_work'])->toBeTrue()
        ->and($state['rules']['acceptance_requires_explicit_authority_transition'])->toBeTrue()
        ->and($state['rules']['production_promotion_remains_human_controlled'])->toBeTrue()
        ->and($state['roadmap_candidates'])->toBeArray()->not->toBeEmpty();

    if ($plan['current_stage']['status'] === 'accepted' && ($plan['active_tranche'] ?? null) === null) {
        expect($state['current']['authored_delivery_state'])->toBe('ACCEPTED')
            ->and($state['current']['derived_delivery_state'])->toBe('ACCEPTED');
    }
});

it('keeps roadmap candidates evidence-gated and separate from active execution authority', function (): void {
    $registry = json_decode(
        (string) file_get_contents(base_path('docs/project/engineering/roadmap-registry.json')),
        true,
        flags: JSON_THROW_ON_ERROR,
    );

    expect($registry['authority'])->toBe('strategic-candidate-registry')
        ->and($registry['execution_source'])->toBe('docs/project/engineering/stage-plan.json')
        ->and($registry['runtime_source'])->toBe('git-and-github-runtime')
        ->and($registry['promotion_rule'])->toContain('never auto-activate implementation')
        ->and($registry['themes'])->toBeArray()->toHaveCount(10);

    foreach ($registry['themes'] as $theme) {
        expect($theme['id'])->toBeString()->not->toBeEmpty()
            ->and($theme['title'])->toBeString()->not->toBeEmpty()
            ->and($theme['state'])->toBeString()->not->toBeEmpty()
            ->and($theme['activation_gate'])->toBeString()->not->toBeEmpty()
            ->and($theme['depends_on'])->toBeArray()
            ->and($theme['evidence_sources'])->toBeArray()->not->toBeEmpty();
    }
});
