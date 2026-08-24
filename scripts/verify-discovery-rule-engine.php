<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$errors = [];
$fail = static function (string $message) use (&$errors): void {
    $errors[] = $message;
};

$required = [
    'app/Domain/Discovery/Contracts/DiscoveryRuleEngine.php',
    'app/Domain/Discovery/Contracts/DiscoveryEntityMapper.php',
    'app/Domain/Discovery/DTO/DiscoveryEntitySnapshot.php',
    'app/Domain/Discovery/DTO/CompiledDiscoveryRule.php',
    'app/Application/Discovery/Rules/DefaultDiscoveryRuleEngine.php',
    'app/Support/Discovery/CanonicalDiscoveryFieldRegistry.php',
    'app/Support/Discovery/CanonicalDiscoveryEntityMapper.php',
    'tests/Unit/DiscoveryRuleEngineTest.php',
    'tests/Architecture/DiscoveryRuleEngineBoundaryTest.php',
    'docs/discovery/rule-engine.md',
];
foreach ($required as $relative) {
    if (! is_file($root.'/'.$relative)) {
        $fail("Missing Stage 16.1 artifact [{$relative}].");
    }
}

try {
    $contracts = json_decode((string) file_get_contents($root.'/docs/project/domain/domain-contracts.json'), true, 512, JSON_THROW_ON_ERROR);
} catch (Throwable $exception) {
    fwrite(STDERR, 'Discovery rule-engine verification failed: '.$exception->getMessage().PHP_EOL);
    exit(1);
}

$engine = $contracts['discovery']['rule_engine'] ?? [];
foreach ([
    'stage' => '16.1',
    'evaluation_target' => 'typed_entity_snapshot',
    'compiles_to_sql' => false,
    'provider_fields_allowed' => false,
    'implicit_sort_tie_breaker' => 'id:asc',
    'set_operator_max_values' => 100,
    'metric_fields_deferred_until_stage' => 17,
] as $key => $expected) {
    if (($engine[$key] ?? null) !== $expected) {
        $fail("Discovery rule-engine authority [{$key}] differs from Stage 16.1 contract.");
    }
}

$registry = (string) @file_get_contents($root.'/app/Support/Discovery/CanonicalDiscoveryFieldRegistry.php');
foreach (['Artist', 'Recording', 'Release', 'Collection'] as $entityType) {
    if (! str_contains($registry, "DiscoverableEntityType::{$entityType}")) {
        $fail("Canonical discovery field registry is missing [{$entityType}].");
    }
}
foreach (['chart_velocity', 'chart_momentum', 'provider_payload', 'spotify_id', 'youtube_id'] as $forbiddenField) {
    if (str_contains($registry, "'{$forbiddenField}'")) {
        $fail("Stage 16.1 registry must not expose deferred/provider field [{$forbiddenField}].");
    }
}

$engineSource = (string) @file_get_contents($root.'/app/Application/Discovery/Rules/DefaultDiscoveryRuleEngine.php');
foreach (['DB::', 'whereRaw(', 'selectRaw(', 'joinRaw(', 'App\\Models\\Providers', 'App\\Contracts\\Providers'] as $forbidden) {
    if (str_contains($engineSource, $forbidden)) {
        $fail("Discovery rule engine violates execution boundary with [{$forbidden}].");
    }
}
foreach (['MAX_SET_VALUES = 100', "new DiscoverySort('id', DiscoverySortDirection::Ascending)", 'CompiledDiscoveryRule'] as $requiredToken) {
    if (! str_contains($engineSource, $requiredToken)) {
        $fail("Discovery rule engine is missing required invariant [{$requiredToken}].");
    }
}

$provider = (string) @file_get_contents($root.'/app/Providers/DiscoveryServiceProvider.php');
foreach (['DiscoveryFieldRegistry::class', 'DiscoveryEntityMapper::class', 'DiscoveryRuleEngine::class'] as $binding) {
    if (! str_contains($provider, $binding)) {
        $fail("DiscoveryServiceProvider is missing discovery binding [{$binding}].");
    }
}

if ($errors !== []) {
    fwrite(STDERR, "Discovery rule-engine verification failed:\n- ".implode("\n- ", array_unique($errors))."\n");
    exit(1);
}

fwrite(STDOUT, "Discovery rule-engine verification passed.\n");
