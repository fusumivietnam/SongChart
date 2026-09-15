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

it('compiles a structural and connectivity-aware project intelligence snapshot without mutating source', function (): void {
    $command = escapeshellarg(PHP_BINARY).' '.escapeshellarg(base_path('scripts/project-intelligence.php')).' --json';
    $lines = [];
    $exitCode = 0;
    exec($command, $lines, $exitCode);

    expect($exitCode)->toBe(0);

    $snapshot = json_decode(implode("\n", $lines), true, flags: JSON_THROW_ON_ERROR);
    $connectivity = $snapshot['metrics']['connectivity'] ?? [];
    $nodes = $snapshot['graph']['nodes'] ?? [];

    expect($snapshot['generated_from_repository'] ?? false)->toBeTrue()
        ->and($snapshot['schema_version'] ?? 0)->toBeGreaterThanOrEqual(2)
        ->and($snapshot['snapshot']['consistency'] ?? null)->toBe('eventual')
        ->and($snapshot['metrics']['class_nodes'] ?? 0)->toBeGreaterThan(0)
        ->and($snapshot['metrics']['route_nodes'] ?? 0)->toBeGreaterThan(0)
        ->and($snapshot['metrics']['use_case_nodes'] ?? 0)->toBeGreaterThan(0)
        ->and($snapshot['metrics']['edges'] ?? 0)->toBeGreaterThan(0)
        ->and($connectivity['application_nodes'] ?? 0)->toBeGreaterThan(0)
        ->and($connectivity['owned_application_nodes'] ?? 0)->toBeGreaterThan(0)
        ->and($connectivity['orphan_candidate_nodes'] ?? -1)->toBeGreaterThanOrEqual(0)
        ->and($connectivity['unclassified_nodes'] ?? -1)->toBeGreaterThanOrEqual(0)
        ->and($connectivity['route_to_use_case_coverage'] ?? -1)->toBeGreaterThanOrEqual(0)
        ->and($connectivity['route_to_use_case_coverage'] ?? 2)->toBeLessThanOrEqual(1)
        ->and($connectivity['job_to_use_case_coverage'] ?? -1)->toBeGreaterThanOrEqual(0)
        ->and($connectivity['job_to_use_case_coverage'] ?? 2)->toBeLessThanOrEqual(1)
        ->and($snapshot['contracts']['versioning'] ?? null)->toBe('docs/project/release/versioning-policy.json');

    $ownedNodes = array_values(array_filter($nodes, static fn (array $node): bool => is_string($node['semantic_owner'] ?? null) && $node['semantic_owner'] !== ''));
    $useCaseNodes = array_values(array_filter($nodes, static fn (array $node): bool => ($node['type'] ?? null) === 'use_case'));

    expect($ownedNodes)->not->toBeEmpty()
        ->and($useCaseNodes)->not->toBeEmpty();

    foreach ($ownedNodes as $node) {
        expect($node)->toHaveKeys(['lifecycle', 'inbound_edges', 'outbound_edges']);
    }
});

it('defines explicit source connectivity closure without auto deletion', function (): void {
    $contract = json_decode((string) file_get_contents(base_path('docs/project/engineering/architecture-graph-contract.json')), true, flags: JSON_THROW_ON_ERROR);

    expect($contract['connectivity']['goal'] ?? '')
        ->toContain('traceable to a semantic owner/use case')
        ->and($contract['connectivity']['orphan_candidate_rule'] ?? '')
        ->toContain('never deletes source automatically')
        ->and($contract['lifecycle_states'] ?? [])
        ->toContain('orphan_candidate', 'legacy_supported', 'infrastructure');
});

it('forbids request-time graph rebuilding by contract', function (): void {
    $contract = json_decode((string) file_get_contents(base_path('docs/project/engineering/architecture-graph-contract.json')), true, flags: JSON_THROW_ON_ERROR);

    expect($contract['snapshot_policy']['request_rule'] ?? '')
        ->toContain('Never scan source or rebuild graph in an HTTP request')
        ->and($contract['snapshot_policy']['single_flight'] ?? '')
        ->toContain('Only one rebuild');
});
