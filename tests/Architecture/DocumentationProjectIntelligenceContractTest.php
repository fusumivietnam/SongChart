<?php

declare(strict_types=1);

it('keeps project intelligence contracts machine readable', function (): void {
    foreach ([
        'docs/project/engineering/project-kernel-contract.json',
        'docs/project/engineering/architecture-graph-contract.json',
        'docs/project/engineering/use-case-contract.schema.json',
        'docs/project/release/versioning-policy.json',
    ] as $relative) {
        $decoded = json_decode((string) file_get_contents(base_path($relative)), true, flags: JSON_THROW_ON_ERROR);

        expect($decoded)->toBeArray()->not->toBeEmpty();
    }
});

it('compiles a structural project intelligence snapshot without mutating source', function (): void {
    $command = escapeshellarg(PHP_BINARY).' '.escapeshellarg(base_path('scripts/project-intelligence.php')).' --json';
    $lines = [];
    $exitCode = 0;
    exec($command, $lines, $exitCode);

    expect($exitCode)->toBe(0);

    $snapshot = json_decode(implode("\n", $lines), true, flags: JSON_THROW_ON_ERROR);

    expect($snapshot['generated_from_repository'] ?? false)->toBeTrue()
        ->and($snapshot['snapshot']['consistency'] ?? null)->toBe('eventual')
        ->and($snapshot['metrics']['class_nodes'] ?? 0)->toBeGreaterThan(0)
        ->and($snapshot['metrics']['route_nodes'] ?? 0)->toBeGreaterThan(0)
        ->and($snapshot['metrics']['edges'] ?? 0)->toBeGreaterThan(0)
        ->and($snapshot['contracts']['versioning'] ?? null)->toBe('docs/project/release/versioning-policy.json');
});

it('forbids request-time graph rebuilding by contract', function (): void {
    $contract = json_decode((string) file_get_contents(base_path('docs/project/engineering/architecture-graph-contract.json')), true, flags: JSON_THROW_ON_ERROR);

    expect($contract['snapshot_policy']['request_rule'] ?? '')
        ->toContain('Never scan source or rebuild graph in an HTTP request')
        ->and($contract['snapshot_policy']['single_flight'] ?? '')
        ->toContain('Only one rebuild');
});
