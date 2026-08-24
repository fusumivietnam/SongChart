<?php

declare(strict_types=1);

use App\Support\Engineering\RepositoryContractResolver;
use Tests\TestCase;

it('resolves repository authorities into deterministic fingerprints', function (): void {
    $resolver = new RepositoryContractResolver(base_path());

    expect($resolver->authorityNames())
        ->toContain(
            'database-test',
            'release-pipeline',
            'runtime-environment',
            'package-schema',
            'model-schema',
            'candidate-verification',
            'repository-compiler',
        );

    foreach ($resolver->authorityNames() as $name) {
        $authority = $resolver->authority($name);

        expect($authority['source'])->toBeString()
            ->and($authority['fingerprint'])->toBeString()->toHaveLength(64)
            ->and($authority['consumers'])->toBeArray();
    }
});

it('keeps the compiled repository graph current', function (): void {
    $resolver = new RepositoryContractResolver(base_path());
    $compiled = $resolver->compileManifest();
    $stored = json_decode(
        (string) file_get_contents(base_path('docs/project/generated/repository-contract-manifest.json')),
        true,
        512,
        JSON_THROW_ON_ERROR,
    );

    expect($stored['graph_fingerprint'])->toBe($compiled['graph_fingerprint'])
        ->and($stored['authorities'])->toBe($compiled['authorities']);
});

it('rejects forbidden raw authority literals in registered consumers', function (): void {
    $resolver = new RepositoryContractResolver(base_path());

    expect($resolver->staleConsumers())->toBe([]);
});

it('resolves impacted authorities from changed authority or consumer paths', function (): void {
    $resolver = new RepositoryContractResolver(base_path());

    expect($resolver->impactedAuthorities(['composer.json']))
        ->toContain('database-test', 'release-pipeline')
        ->and($resolver->impactedAuthorities(['docs/project/stack/database-test-contract.json']))
        ->toContain('database-test');
});

it('exposes contract diagnostics through the SongChart doctor command', function (): void {
    /** @var TestCase $this */
    $this->artisan('songchart:doctor', ['--contract' => 'database-test'])
        ->expectsOutputToContain('Repository authority: database-test')
        ->expectsOutputToContain('Authority consumers are resolved without forbidden raw literals.')
        ->assertSuccessful();
});

it('treats the compiled manifest as a derived artifact of the exact current tree', function (): void {
    $root = dirname(__DIR__, 2);
    $compiler = json_decode(
        (string) file_get_contents($root.'/docs/project/engineering/repository-contract-compiler.json'),
        true,
        512,
        JSON_THROW_ON_ERROR,
    );
    $script = (string) file_get_contents($root.'/scripts/compile-repository-contracts.php');

    expect($compiler['policy']['derived_manifest_compiled_on_exact_target_tree'])->toBeTrue()
        ->and($compiler['policy']['packaged_generated_manifest_is_not_cross_environment_authority'])->toBeTrue()
        ->and($script)->toContain('--refresh-check')
        ->and($script)->toContain('exact source tree');
});

it('closes the verification consumer graph without unowned consumers', function (): void {
    $resolver = new RepositoryContractResolver(base_path());
    $consumers = $resolver->verificationConsumers();

    expect($resolver->verificationConsumerGraphViolations())->toBe([])
        ->and($resolver->verifierExecutionOwners())->toBe([])
        ->and(count($consumers))->toBeGreaterThan(0);

    foreach ($consumers as $consumer) {
        expect($consumer['rule'] !== '')->toBeTrue()
            ->and($consumer['semantic_authorities'] !== [])->toBeTrue();
    }
});
