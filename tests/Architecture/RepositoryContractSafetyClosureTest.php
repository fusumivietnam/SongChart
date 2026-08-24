<?php

declare(strict_types=1);

use App\Support\Engineering\RepositoryContractResolver;

it('keeps historical regressions mapped to permanent machine guards', function (): void {
    $root = dirname(__DIR__, 2);
    $ledger = json_decode((string) file_get_contents($root.'/docs/project/engineering/regression-ledger.json'), true, 512, JSON_THROW_ON_ERROR);

    expect($ledger['policy']['closure_rule'])->toContain('permanent machine-enforced guard')
        ->and($ledger['policy']['phpstan_baseline_widening_allowed'])->toBeFalse()
        ->and($ledger['regressions'] !== [])->toBeTrue();

    foreach ($ledger['regressions'] as $regression) {
        expect($regression['status'])->toBe('guarded')
            ->and($regression['permanent_guards'] !== [])->toBeTrue();
    }
});

it('keeps package model runtime and installer contracts machine readable', function (): void {
    $root = dirname(__DIR__, 2);

    foreach ([
        'docs/project/stack/package-schema-contracts.json',
        'docs/project/domain/model-schema-contracts.json',
        'docs/project/stack/runtime-environments.json',
        'docs/project/engineering/installer-contract.json',
    ] as $relative) {
        $decoded = json_decode((string) file_get_contents($root.'/'.$relative), true, 512, JSON_THROW_ON_ERROR);

        expect($decoded)
            ->toBeArray()
            ->and($decoded['schema_version'] ?? null)
            ->toBeInt()
            ->toBeGreaterThanOrEqual(1);
    }
});

it('starts the canonical PostgreSQL full suite from a clean schema', function (): void {
    $root = dirname(__DIR__, 2);
    $composer = json_decode((string) file_get_contents($root.'/composer.json'), true, 512, JSON_THROW_ON_ERROR);
    $resolver = new RepositoryContractResolver($root);
    $databaseContract = $resolver->value('database-test');
    $releaseContract = $resolver->value('release-pipeline');

    expect($composer['scripts']['test:postgres'])->toBe($databaseContract['scripts']['test:postgres'])
        ->and($composer['scripts']['test:feature'])->toBe($databaseContract['scripts']['test:feature'])
        ->and(array_key_exists('release:verify', $composer['scripts']))->toBeFalse()
        ->and($composer['scripts']['stage:verify'])->toBe($releaseContract['stage_steps'])
        ->and($composer['scripts']['canonical:verify'])->toBe($releaseContract['canonical_steps']);
});

it('forbids ambient database feature test execution in release orchestration', function (): void {
    $root = dirname(__DIR__, 2);
    $source = (string) file_get_contents($root.'/scripts/verify-release-orchestration.php');

    expect($source)
        ->toContain('php\\s+composer\\.phar')
        ->toContain('php\\s+artisan\\s+test\\s+tests')
        ->toContain('composer install --no-interaction --prefer-dist --no-progress');
});

it('keeps database and runtime verifiers on one shared PostgreSQL test contract', function (): void {
    $root = dirname(__DIR__, 2);
    $contract = (new RepositoryContractResolver($root))->value('database-test');
    $composer = json_decode((string) file_get_contents($root.'/composer.json'), true, 512, JSON_THROW_ON_ERROR);
    $databaseVerifier = (string) file_get_contents($root.'/scripts/verify-database-authority.php');
    $runtimeVerifier = (string) file_get_contents($root.'/scripts/verify-runtime-authority-closure.php');

    foreach (['test', 'test:feature', 'test:postgres'] as $script) {
        expect($composer['scripts'][$script])->toBe($contract['scripts'][$script]);
    }

    expect($contract['scripts']['test:feature'])
        ->toContain('--prepare-schema')
        ->and($contract['scripts']['test:postgres'])
        ->toContain('--prepare-schema')
        ->and($databaseVerifier)
        ->toContain('RepositoryContractResolver')
        ->and($runtimeVerifier)
        ->toContain('RepositoryContractResolver');
});

it('keeps release preflight ordering on one shared contract', function (): void {
    $root = dirname(__DIR__, 2);
    $contract = (new RepositoryContractResolver($root))->value('release-pipeline');
    $composer = json_decode((string) file_get_contents($root.'/composer.json'), true, 512, JSON_THROW_ON_ERROR);
    $performanceVerifier = (string) file_get_contents($root.'/scripts/verify-performance-baseline.php');
    $releaseVerifier = (string) file_get_contents($root.'/scripts/verify-release-orchestration.php');

    expect(array_key_exists('release:verify', $composer['scripts']))->toBeFalse()
        ->and($composer['scripts']['stage:verify'])->toBe($contract['stage_steps'])
        ->and($composer['scripts']['canonical:verify'])->toBe($contract['canonical_steps'])
        ->and($contract['rules']['stage_runs_once_via_canonical'])->toBeTrue()
        ->and($contract['rules']['canonical_shell_must_not_repeat_stage_gates'])->toBeTrue()
        ->and($performanceVerifier)->toContain('RepositoryContractResolver')
        ->and($releaseVerifier)->toContain('RepositoryContractResolver');
});

it('preserves exact PostgreSQL test failure evidence next to wrapper failures', function (): void {
    $root = dirname(__DIR__, 2);
    $runner = (string) file_get_contents($root.'/scripts/run-database-tests.php');

    expect($runner)
        ->toContain('[SongChart test evidence]')
        ->toContain('postgres-test-last-failure.log')
        ->toContain('stream_select')
        ->toContain('[REDACTED]');
});
