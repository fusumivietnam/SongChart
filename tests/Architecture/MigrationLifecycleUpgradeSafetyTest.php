<?php

declare(strict_types=1);

it('freezes historical migrations and owns corrections through forward migrations', function (): void {
    $root = dirname(__DIR__, 2);
    $contract = json_decode(
        (string) file_get_contents($root.'/docs/project/stack/migration-lifecycle-contract.json'),
        true,
        512,
        JSON_THROW_ON_ERROR,
    );
    $historical = (string) file_get_contents($root.'/database/migrations/2026_08_13_000100_create_activity_log_table.php');
    $forward = (string) file_get_contents($root.'/database/migrations/2026_08_16_000100_add_attribute_changes_to_activity_log_if_missing.php');

    expect($contract['policy']['historical_migrations_are_immutable'] ?? false)->toBeTrue()
        ->and($contract['policy']['schema_corrections_are_forward_only'] ?? false)->toBeTrue()
        ->and(str_contains($historical, "json('attribute_changes')"))->toBeFalse()
        ->and($forward)->toContain("Schema::hasColumn('activity_log', 'attribute_changes')")
        ->and($forward)->toContain("json('attribute_changes')->nullable()")
        ->and(str_contains($forward, 'dropColumn('))->toBeFalse();
});

it('keeps the canonical upgrade lane separate from fresh schema tests', function (): void {
    $composer = json_decode((string) file_get_contents(base_path('composer.json')), true, 512, JSON_THROW_ON_ERROR);
    $canonical = $composer['scripts']['canonical:verify'] ?? [];

    expect($composer['scripts']['migration-lifecycle:verify'] ?? null)
        ->toBe('@php scripts/verify-migration-lifecycle.php')
        ->and($composer['scripts']['migration-upgrade:verify'] ?? null)
        ->toBe('@php scripts/run-migration-upgrade-test.php')
        ->and($canonical)->toContain('@migration-upgrade:verify')
        ->and(count(array_keys($canonical, '@migration-upgrade:verify', true)))->toBe(1);
});

it('seals migration history only from the exact canonical target', function (): void {
    $root = dirname(__DIR__, 2);
    $contract = json_decode(
        (string) file_get_contents($root.'/docs/project/stack/migration-lifecycle-contract.json'),
        true,
        512,
        JSON_THROW_ON_ERROR,
    );
    $sealer = (string) file_get_contents($root.'/scripts/seal-migration-lifecycle.php');

    expect($contract['baseline']['mode'] ?? null)->toBe('canonical-target-sealed')
        ->and($contract['policy']['retroactive_packaging_artifact_fingerprints_forbidden'] ?? false)->toBeTrue()
        ->and($sealer)->toContain('exact-canonical-target-after-locked-pint-normalization')
        ->and($sealer)->toContain('refusing to overwrite it');
});
