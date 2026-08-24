<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$errors = [];

$required = [
    'app/Domain/Audit/Contracts/PrivilegedAuditLogger.php',
    'app/Support/Audit/SpatiePrivilegedAuditLogger.php',
    'app/Support/Admin/PrivilegedAuditConsole.php',
    'app/Support/Admin/PrivilegedUserAdministration.php',
    'app/Http/Controllers/Admin/PrivilegedAuditController.php',
    'app/Console/Commands/SetUserRoleCommand.php',
    'app/Console/Commands/SetUserActiveStateCommand.php',
    'config/activitylog.php',
    'docs/project/stack/package-schema-contracts.json',
    'docs/project/stack/migration-lifecycle-contract.json',
    'resources/views/admin/audit/index.blade.php',
];

foreach ($required as $relative) {
    if (! is_file($root.'/'.$relative)) {
        $errors[] = "Missing privileged-audit authority [{$relative}].";
    }
}

$composer = json_decode((string) file_get_contents($root.'/composer.json'), true);
if (($composer['require']['spatie/laravel-activitylog'] ?? null) !== '^5.0') {
    $errors[] = 'Composer must require spatie/laravel-activitylog:^5.0.';
}

$registry = json_decode((string) file_get_contents($root.'/docs/project/stack/package-registry.json'), true);
$package = $registry['packages']['spatie/laravel-activitylog'] ?? null;
if (! is_array($package) || ($package['capability_owner'] ?? null) !== 'privileged-audit') {
    $errors[] = 'Package registry must assign activitylog to privileged-audit.';
}
if (($package['usage_policy'] ?? null) !== 'Explicit privileged/business events only; automatic broad model-event logging is forbidden.') {
    $errors[] = 'Activitylog package policy must prohibit broad automatic model logging.';
}

$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root.'/app'));
foreach ($iterator as $file) {
    if (! $file->isFile() || $file->getExtension() !== 'php') {
        continue;
    }

    if (str_contains((string) file_get_contents($file->getPathname()), 'LogsActivity')) {
        $errors[] = 'Application models/actions must not use broad LogsActivity automatic event logging.';
        break;
    }
}

$schemaContracts = json_decode(
    (string) file_get_contents($root.'/docs/project/stack/package-schema-contracts.json'),
    true,
    512,
    JSON_THROW_ON_ERROR,
);
$activitySchema = $schemaContracts['contracts']['spatie/laravel-activitylog'] ?? null;
if (! is_array($activitySchema)) {
    $errors[] = 'Privileged audit requires the Activitylog package schema contract.';
} else {
    foreach ([
        'subject_id' => ['kind' => 'char', 'length' => 26, 'nullable' => true],
        'causer_id' => ['kind' => 'char', 'length' => 26, 'nullable' => true],
        'attribute_changes' => ['kind' => 'json', 'nullable' => true],
        'properties' => ['kind' => 'json', 'nullable' => true],
        'batch_uuid' => ['kind' => 'uuid', 'nullable' => true],
    ] as $column => $expected) {
        if (($activitySchema['required_columns'][$column] ?? null) !== $expected) {
            $errors[] = "Privileged audit package schema mismatch [{$column}].";
        }
    }

    $lifecycle = $activitySchema['migration_lifecycle'] ?? null;
    if (! is_array($lifecycle)
        || ($lifecycle['historical_create_migration'] ?? null) !== 'database/migrations/2026_08_13_000100_create_activity_log_table.php'
        || ! in_array(
            'database/migrations/2026_08_16_000100_add_attribute_changes_to_activity_log_if_missing.php',
            $lifecycle['forward_corrections'] ?? [],
            true,
        )
    ) {
        $errors[] = 'Privileged audit schema lifecycle must resolve Activitylog corrections through the registered forward migration.';
    }
}

$migrationLifecycle = json_decode(
    (string) file_get_contents($root.'/docs/project/stack/migration-lifecycle-contract.json'),
    true,
    512,
    JSON_THROW_ON_ERROR,
);
if (($migrationLifecycle['policy']['historical_migrations_are_immutable'] ?? false) !== true
    || ($migrationLifecycle['policy']['schema_corrections_are_forward_only'] ?? false) !== true
) {
    $errors[] = 'Privileged audit must respect immutable historical migrations and forward-only schema corrections.';
}

$routes = (string) file_get_contents($root.'/routes/web.php');
if (! str_contains($routes, "Route::get('/audit', PrivilegedAuditController::class)") || ! str_contains($routes, "middleware('can:view-audit')")) {
    $errors[] = 'Privileged audit viewer must be protected by the view-audit gate.';
}

$providerMutations = (string) file_get_contents($root.'/app/Support/Providers/Operations/ProviderMutationService.php');
foreach (["event: 'provider.'.\$action", "event: 'provider-import.'.\$action"] as $signal) {
    if (! str_contains($providerMutations, $signal)) {
        $errors[] = "Provider privileged audit missing [{$signal}].";
    }
}

$identity = (string) file_get_contents($root.'/app/Http/Controllers/Admin/IdentityConflictReviewController.php');
if (! str_contains($identity, "event: 'identity-conflict.'.\$action->value")) {
    $errors[] = 'Identity conflict decisions must emit privileged audit events.';
}

$config = (string) file_get_contents($root.'/config/activitylog.php');
foreach (['password', 'two_factor_secret', 'two_factor_recovery_codes'] as $secret) {
    if (! str_contains($config, "'{$secret}'")) {
        $errors[] = "Activitylog config must globally exclude sensitive attribute [{$secret}].";
    }
}

if ($errors !== []) {
    fwrite(STDERR, "Privileged audit verification failed:\n- ".implode("\n- ", array_unique($errors)).PHP_EOL);
    exit(1);
}

fwrite(STDOUT, 'Privileged operations and audit contract passed.'.PHP_EOL);
