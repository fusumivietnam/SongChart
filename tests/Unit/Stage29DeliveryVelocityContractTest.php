<?php

declare(strict_types=1);

use Symfony\Component\Process\Process;

it('keeps draft pull request verification focused and promotion closure full', function (): void {
    $root = dirname(__DIR__, 2);
    $fast = (string) file_get_contents($root.'/.github/workflows/pr-fast.yml');
    $closure = (string) file_get_contents($root.'/.github/workflows/auto-closure.yml');

    expect($fast)
        ->toContain("name: SongChart PR Fast Check")
        ->toContain("github.actor != 'github-actions[bot]'")
        ->toContain('./songchart impact --verify')
        ->toContain('Prepare deterministic authority ephemerally')
        ->not->toContain('git push')
        ->not->toContain('uses: ./.github/workflows/tests.yml')
        ->not->toContain('./songchart verify');

    expect($closure)
        ->toContain("name: SongChart Promotion Closure")
        ->toContain('- ready_for_review')
        ->toContain('- synchronize')
        ->toContain('github.event.pull_request.draft == false')
        ->toContain('uses: ./.github/workflows/tests.yml')
        ->toContain('./songchart verify')
        ->toContain('Prove PR head still matches verified SHA')
        ->toContain('Prove canonical verification preserved exact tree');
});

it('registers fast and promotion lanes without replacing existing verification owners', function (): void {
    $root = dirname(__DIR__, 2);
    $kernel = json_decode(
        (string) file_get_contents($root.'/docs/project/engineering/delivery-kernel.json'),
        true,
        flags: JSON_THROW_ON_ERROR,
    );

    expect($kernel['schema_version'])->toBe(4)
        ->and($kernel['ci_lanes']['fast_pr']['delegate'])->toBe('./songchart impact --verify')
        ->and($kernel['ci_lanes']['fast_pr']['push_generated_authority'])->toBeFalse()
        ->and($kernel['ci_lanes']['promotion_closure']['delegate'])->toBe('./songchart verify')
        ->and($kernel['ci_lanes']['promotion_closure']['gate_profile'])->toBe('G4')
        ->and($kernel['github_auto_closure']['draft_pr_full_closure'])->toBeFalse()
        ->and($kernel['github_auto_closure']['ready_for_review_is_promotion_boundary'])->toBeTrue()
        ->and($kernel['github_auto_closure']['non_draft_synchronize_reruns_closure'])->toBeTrue()
        ->and($kernel['roadmap_state']['candidate_auto_activation'])->toBeFalse()
        ->and($kernel['roadmap_state']['authored_acceptance_required'])->toBeTrue();
});

it('compiles roadmap runtime state without turning a clean tree into verification evidence', function (): void {
    $root = dirname(__DIR__, 2);

    $unverified = new Process([PHP_BINARY, $root.'/scripts/roadmap-state.php', '--json'], $root);
    $unverified->run();
    expect($unverified->isSuccessful())->toBeTrue($unverified->getErrorOutput());
    $unverifiedState = json_decode($unverified->getOutput(), true, flags: JSON_THROW_ON_ERROR);

    expect($unverifiedState['current']['stage']['id'])->toBe('29.0')
        ->and($unverifiedState['current']['authored_delivery_state'])->toBe('IMPLEMENTING')
        ->and($unverifiedState['current']['derived_delivery_state'])->toBe('IMPLEMENTING')
        ->and($unverifiedState['verification']['status'])->toBe('unresolved')
        ->and($unverifiedState['rules']['runtime_facts_are_not_authored_progress'])->toBeTrue()
        ->and($unverifiedState['rules']['registry_does_not_auto_activate_work'])->toBeTrue();

    $head = trim((string) shell_exec('cd '.escapeshellarg($root).' && git rev-parse HEAD'));
    $verified = new Process([PHP_BINARY, $root.'/scripts/roadmap-state.php', '--json'], $root, [
        'SONGCHART_VERIFICATION_STATUS' => 'passed',
        'SONGCHART_VERIFICATION_SHA' => $head,
        'SONGCHART_VERIFICATION_RUN_ID' => '12345',
    ]);
    $verified->run();
    expect($verified->isSuccessful())->toBeTrue($verified->getErrorOutput());
    $verifiedState = json_decode($verified->getOutput(), true, flags: JSON_THROW_ON_ERROR);

    expect($verifiedState['current']['derived_delivery_state'])->toBe('CI_VERIFIED')
        ->and($verifiedState['current']['next_transition'])->toBe('acceptance_candidate')
        ->and($verifiedState['verification']['matches_current_head'])->toBeTrue();
});

it('keeps roadmap candidates strategic and non activating', function (): void {
    $root = dirname(__DIR__, 2);
    $registry = json_decode(
        (string) file_get_contents($root.'/docs/project/engineering/roadmap-registry.json'),
        true,
        flags: JSON_THROW_ON_ERROR,
    );

    expect($registry['authority'])->toBe('strategic-candidate-registry')
        ->and($registry['execution_source'])->toBe('docs/project/engineering/stage-plan.json')
        ->and($registry['promotion_rule'])->toContain('never auto-activate');

    foreach ($registry['themes'] as $theme) {
        expect($theme['id'])->toBeString()->not->toBeEmpty()
            ->and($theme['state'])->toBeString()->not->toBeEmpty()
            ->and($theme['activation_gate'])->toBeString()->not->toBeEmpty();
    }
});

it('surfaces roadmap state through the existing internal delivery kernel facade', function (): void {
    $root = dirname(__DIR__, 2);
    $script = (string) file_get_contents($root.'/scripts/delivery-kernel.sh');

    expect($script)
        ->toContain('ROADMAP_STATE="$ROOT/scripts/roadmap-state.php"')
        ->toContain('php "$ROADMAP_STATE"')
        ->toContain('exec "$ROOT/songchart" impact --verify')
        ->toContain('exec "$ROOT/songchart" close')
        ->not->toContain('git push');
});
