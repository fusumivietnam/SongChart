<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$errors = [];

$script = $root.'/scripts/verify-stage-16.5-focused.ps1';
if (! is_file($script)) {
    $errors[] = 'Missing Stage 16.5 canonical focused-test launcher.';
} else {
    $source = (string) file_get_contents($script);
    foreach ([
        'compose.verify.yml',
        'up -d postgres redis',
        'composer install --no-interaction --prefer-dist --no-progress',
        'scripts/run-database-tests.php',
        "'postgres'",
        "'--prepare-schema'",
        'tests/Feature/PrivilegedAuditTest.php',
    ] as $signal) {
        if (! str_contains($source, $signal)) {
            $errors[] = "Focused-test launcher missing [{$signal}].";
        }
    }

    if (str_contains($source, 'php artisan test tests/Feature/PrivilegedAuditTest.php')) {
        $errors[] = 'Database-backed privileged audit feature tests must not run directly against the ambient host database.';
    }
}

$runner = (string) file_get_contents($root.'/scripts/run-database-tests.php');
foreach ([
    "in_array('--prepare-schema', \$argv, true)",
    "'artisan', 'migrate:fresh', '--force', '--ansi'",
    "'Post-migration PostgreSQL test database safety verification failed.'",
] as $signal) {
    if (! str_contains($runner, $signal)) {
        $errors[] = "PostgreSQL test runner schema-preparation contract missing [{$signal}].";
    }
}

$auditFeature = (string) file_get_contents($root.'/tests/Feature/PrivilegedAuditTest.php');
if (
    ! str_contains($auditFeature, 'use Illuminate\\Foundation\\Testing\\RefreshDatabase;')
    || ! str_contains($auditFeature, 'uses(RefreshDatabase::class);')
) {
    $errors[] = 'PrivilegedAuditTest must use Laravel RefreshDatabase so focused execution cannot contaminate the subsequent canonical full suite.';
}

if ($errors !== []) {
    fwrite(STDERR, "Stage 16.5 focused-test lane verification failed:\n- ".implode("\n- ", $errors).PHP_EOL);
    exit(1);
}

fwrite(STDOUT, 'Stage 16.5 focused tests are bound to canonical PostgreSQL test authority.'.PHP_EOL);
