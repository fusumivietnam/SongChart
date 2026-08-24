<?php

declare(strict_types=1);

it('governs activitylog as explicit privileged business audit only', function (): void {
    $root = dirname(__DIR__, 2);
    $composer = json_decode((string) file_get_contents($root.'/composer.json'), true, 512, JSON_THROW_ON_ERROR);
    $registry = json_decode((string) file_get_contents($root.'/docs/project/stack/package-registry.json'), true, 512, JSON_THROW_ON_ERROR);
    $schema = json_decode((string) file_get_contents($root.'/docs/project/stack/package-schema-contracts.json'), true, 512, JSON_THROW_ON_ERROR);
    $lifecycle = json_decode((string) file_get_contents($root.'/docs/project/stack/migration-lifecycle-contract.json'), true, 512, JSON_THROW_ON_ERROR);
    $activity = $schema['contracts']['spatie/laravel-activitylog'];

    expect($composer['require']['spatie/laravel-activitylog'])->toBe('^5.0')
        ->and($registry['packages']['spatie/laravel-activitylog']['capability_owner'])->toBe('privileged-audit')
        ->and($registry['packages']['spatie/laravel-activitylog']['usage_policy'])
        ->toContain('automatic broad model-event logging is forbidden')
        ->and($activity['required_columns']['subject_id'])->toBe(['kind' => 'char', 'length' => 26, 'nullable' => true])
        ->and($activity['required_columns']['causer_id'])->toBe(['kind' => 'char', 'length' => 26, 'nullable' => true])
        ->and($activity['required_columns']['attribute_changes'])->toBe(['kind' => 'json', 'nullable' => true])
        ->and($activity['required_columns']['properties'])->toBe(['kind' => 'json', 'nullable' => true])
        ->and($activity['migration_lifecycle']['forward_corrections'])
        ->toContain('database/migrations/2026_08_16_000100_add_attribute_changes_to_activity_log_if_missing.php')
        ->and($lifecycle['policy']['historical_migrations_are_immutable'])->toBeTrue()
        ->and($lifecycle['policy']['schema_corrections_are_forward_only'])->toBeTrue();
});

it('keeps privileged audit explicit instead of attaching automatic logging traits', function (): void {
    $root = dirname(__DIR__, 2);
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root.'/app'));

    foreach ($iterator as $file) {
        if (! $file->isFile() || $file->getExtension() !== 'php') {
            continue;
        }

        expect(str_contains((string) file_get_contents($file->getPathname()), 'LogsActivity'))
            ->toBeFalse();
    }
});

it('isolates privileged audit feature fixtures from the canonical database', function (): void {
    $root = dirname(__DIR__, 2);
    $feature = (string) file_get_contents($root.'/tests/Feature/PrivilegedAuditTest.php');

    expect($feature)
        ->toContain('use Illuminate\Foundation\Testing\RefreshDatabase;')
        ->toContain('uses(RefreshDatabase::class);');
});
