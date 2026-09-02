<?php

declare(strict_types=1);

it('keeps delivery kernel vocabulary bounded and strangler-safe', function (): void {
    $root = dirname(__DIR__, 2);
    $contract = json_decode(
        (string) file_get_contents($root.'/docs/project/engineering/delivery-kernel.json'),
        true,
        flags: JSON_THROW_ON_ERROR,
    );

    expect($contract['schema_version'])->toBe(2)
        ->and(array_keys($contract['risk_classes']))->toBe(['R1', 'R2', 'R3', 'R4', 'R5', 'R6'])
        ->and(array_keys($contract['gate_profiles']))->toBe(['G0', 'G1', 'G2', 'G3', 'G4'])
        ->and($contract['lifecycle'])->toBe([
            'PLANNED',
            'IMPLEMENTING',
            'CHECKED',
            'CLOSED',
            'CI_VERIFIED',
            'ACCEPTED',
            'RELEASED',
        ])
        ->and($contract['principles']['existing_verifiers_remain_authoritative'])->toBeTrue()
        ->and($contract['migration_policy']['strategy'])->toBe('strangler')
        ->and($contract['migration_policy']['do_not_delete_existing_gate_until_equivalent_kernel_consumer_is_proven'])->toBeTrue()
        ->and($contract['migration_policy']['compiled_command_surface_cutover_requires_reconcile'])->toBeTrue();
});

it('keeps the delivery kernel facade delegating instead of reimplementing closure', function (): void {
    $root = dirname(__DIR__, 2);
    $script = (string) file_get_contents($root.'/scripts/delivery-kernel.sh');

    expect($script)
        ->toContain('exec "$ROOT/songchart" impact "$@"')
        ->toContain('exec "$ROOT/songchart" impact --verify')
        ->toContain('exec "$ROOT/songchart" close')
        ->toContain('exec "$ROOT/songchart" ai doctor "$@"')
        ->toContain('Promotion is not granted locally.')
        ->toContain('clean Git state alone never implies CHECKED/CLOSED')
        ->not->toContain('composer canonical:verify')
        ->not->toContain('composer stage:verify');
});

it('keeps the GitHub mobile control plane tap-only and exact-SHA read-only', function (): void {
    $root = dirname(__DIR__, 2);
    $workflow = (string) file_get_contents($root.'/.github/workflows/songchart-mobile.yml');
    $contract = json_decode(
        (string) file_get_contents($root.'/docs/project/engineering/delivery-kernel.json'),
        true,
        flags: JSON_THROW_ON_ERROR,
    );
    $mobile = $contract['github_mobile_control_plane'];

    expect($mobile['actions'])->toBe(['check', 'close'])
        ->and($mobile['check_delegate'])->toBe('./mobile check --verbose')
        ->and($mobile['close_delegate'])->toBe('./songchart verify')
        ->and($mobile['close_is_read_only_exact_sha'])->toBeTrue()
        ->and($mobile['must_prove_head_unchanged'])->toBeTrue()
        ->and($mobile['must_prove_tracked_tree_clean_after_verification'])->toBeTrue()
        ->and($mobile['may_commit_generated_authority'])->toBeFalse()
        ->and($mobile['may_push_source_changes'])->toBeFalse()
        ->and($workflow)
        ->toContain('workflow_dispatch:')
        ->toContain('- check')
        ->toContain('- close')
        ->toContain('SONGCHART_TARGET_SHA: ${{ github.event.pull_request.head.sha || github.sha }}')
        ->toContain('ref: ${{ env.SONGCHART_TARGET_SHA }}')
        ->toContain("github.head_ref == 'stage-19.0-delivery-kernel-hardening'")
        ->toContain('run: ./mobile check --verbose')
        ->toContain('run: ./songchart verify')
        ->toContain('test "$(git rev-parse HEAD)" = "$SONGCHART_MOBILE_SHA"')
        ->not->toContain('./mobile close')
        ->not->toContain('git commit')
        ->not->toContain('git push');
});
