<?php

use Illuminate\Support\Arr;

it('keeps delivery kernel vocabulary bounded and strangler-safe', function (): void {
    $path = base_path('docs/project/engineering/delivery-kernel.json');
    $contract = json_decode((string) file_get_contents($path), true, flags: JSON_THROW_ON_ERROR);

    expect($contract['schema_version'])->toBe(1)
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
        ->and(Arr::get($contract, 'principles.existing_verifiers_remain_authoritative'))->toBeTrue()
        ->and(Arr::get($contract, 'migration_policy.strategy'))->toBe('strangler')
        ->and(Arr::get($contract, 'migration_policy.do_not_delete_existing_gate_until_equivalent_kernel_consumer_is_proven'))->toBeTrue();
});

it('keeps the delivery kernel facade delegating instead of reimplementing closure', function (): void {
    $script = (string) file_get_contents(base_path('scripts/delivery-kernel.sh'));

    expect($script)
        ->toContain('exec "$ROOT/songchart" impact "$@"')
        ->toContain('exec "$ROOT/songchart" impact --verify')
        ->toContain('GitHub PR CI must pass on this exact SHA before merge.')
        ->not->toContain('composer canonical:verify')
        ->not->toContain('composer stage:verify');
});
