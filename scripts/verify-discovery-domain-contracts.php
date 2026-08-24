<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$errors = [];
$fail = static function (string $message) use (&$errors): void {
    $errors[] = $message;
};

try {
    $contracts = json_decode((string) file_get_contents($root.'/docs/project/domain/domain-contracts.json'), true, flags: JSON_THROW_ON_ERROR);
} catch (Throwable $exception) {
    fwrite(STDERR, 'Discovery domain contract verification failed: '.$exception->getMessage().PHP_EOL);
    exit(1);
}

$discovery = is_array($contracts['discovery'] ?? null) ? $contracts['discovery'] : [];
if (($discovery['schema_version'] ?? null) !== 1) {
    $fail('Discovery contract schema_version must be 1.');
}

$enumValues = static function (string $relative) use ($root): array {
    $source = (string) file_get_contents($root.'/'.$relative);
    preg_match_all("/case\\s+\\w+\\s*=\\s*'([^']+)'/", $source, $matches);

    return $matches[1] ?? [];
};

$comparisons = [
    'channel.modes' => ['app/Domain/Discovery/Enums/DiscoveryChannelMode.php', $discovery['channel']['modes'] ?? []],
    'channel.statuses' => ['app/Domain/Discovery/Enums/DiscoveryChannelStatus.php', $discovery['channel']['statuses'] ?? []],
    'channel.discoverable_entity_types' => ['app/Domain/Discovery/Enums/DiscoverableEntityType.php', $discovery['channel']['discoverable_entity_types'] ?? []],
    'channel.layouts' => ['app/Domain/Discovery/Enums/DiscoveryLayout.php', $discovery['channel']['layouts'] ?? []],
    'channel.surfaces' => ['app/Domain/Discovery/Enums/DiscoverySurface.php', $discovery['channel']['surfaces'] ?? []],
    'rules.operators' => ['app/Domain/Discovery/Enums/DiscoveryRuleOperator.php', $discovery['rules']['operators'] ?? []],
    'permissions' => ['app/Domain/Discovery/Enums/DiscoveryCapability.php', $discovery['permissions'] ?? []],
];
foreach ($comparisons as $name => [$relative, $declared]) {
    $actual = $enumValues($relative);
    sort($actual);
    if (is_array($declared)) {
        sort($declared);
    }
    if ($actual !== $declared) {
        $fail("Discovery enum values differ from contract [{$name}].");
    }
}

$boundary = $discovery['boundary'] ?? [];
foreach ([
    'mutates_canonical_catalog' => false,
    'calls_provider_apis' => false,
    'public_request_executes_rules' => false,
] as $key => $expected) {
    if (($boundary[$key] ?? null) !== $expected) {
        $fail("Discovery boundary [{$key}] must remain false.");
    }
}
if (($discovery['rules']['raw_sql_allowed'] ?? null) !== false) {
    $fail('Discovery rules must prohibit raw SQL.');
}
if (($discovery['rules']['arbitrary_fields_allowed'] ?? null) !== false) {
    $fail('Discovery rules must require a field registry.');
}

$migrationPath = $root.'/database/migrations/2026_08_10_000200_create_discovery_domain_tables.php';
$migration = is_file($migrationPath) ? (string) file_get_contents($migrationPath) : '';
foreach ($discovery['tables'] ?? [] as $table) {
    if (! is_string($table) || ! str_contains($migration, "Schema::create('{$table}'")) {
        $fail("Discovery storage table [{$table}] is missing from the discovery migration.");
    }
}
foreach (['rules', 'sorts', 'presentation', 'payload'] as $jsonbColumn) {
    if (! str_contains($migration, "jsonb('{$jsonbColumn}')")) {
        $fail("Discovery flexible column [{$jsonbColumn}] must use PostgreSQL JSONB.");
    }
}

$domainFiles = array_merge(
    glob($root.'/app/Domain/Discovery/*.php') ?: [],
    glob($root.'/app/Domain/Discovery/*/*.php') ?: [],
);
foreach ($domainFiles as $path) {
    $source = (string) file_get_contents($path);
    foreach (['Illuminate\\Database\\Eloquent', 'App\\Models\\Providers', 'App\\Contracts\\Providers', 'DB::raw(', 'whereRaw(', 'selectRaw('] as $forbidden) {
        if (str_contains($source, $forbidden)) {
            $fail(basename($path)." violates discovery domain boundary with [{$forbidden}].");
        }
    }
}

foreach ([
    'app/Domain/Discovery/Contracts/DiscoveryFieldRegistry.php',
    'app/Domain/Discovery/Contracts/DiscoveryChannelRepository.php',
    'app/Domain/Discovery/Contracts/DiscoveryProjectionReader.php',
] as $contractFile) {
    if (! is_file($root.'/'.$contractFile)) {
        $fail("Missing discovery contract file [{$contractFile}].");
    }
}

if ($errors !== []) {
    fwrite(STDERR, "Discovery domain contract verification failed:\n- ".implode("\n- ", array_unique($errors))."\n");
    exit(1);
}

fwrite(STDOUT, "Discovery domain contract verification passed.\n");
