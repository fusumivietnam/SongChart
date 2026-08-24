<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$registryPath = $root.'/docs/project/stack/package-registry.json';
$composerPath = $root.'/composer.json';

if (! is_file($registryPath) || ! is_file($composerPath)) {
    fwrite(STDERR, "Package governance authority is missing.\n");
    exit(1);
}

$registry = json_decode((string) file_get_contents($registryPath), true, 512, JSON_THROW_ON_ERROR);
$composer = json_decode((string) file_get_contents($composerPath), true, 512, JSON_THROW_ON_ERROR);

$errors = [];
$registered = $registry['packages'] ?? [];
foreach (['require', 'require-dev'] as $section) {
    foreach (($composer[$section] ?? []) as $package => $constraint) {
        if ($package === 'php') {

            continue;
        }

        if (! isset($registered[$package])) {
            $errors[] = "Composer package [{$package}] is not registered.";

            continue;
        }

        if (($registered[$package]['constraint'] ?? null) !== $constraint) {
            $errors[] = "Package [{$package}] constraint differs from package-registry.json.";
        }
    }
}

foreach ($registered as $package => $definition) {
    $inComposer = array_key_exists($package, $composer['require'] ?? [])
        || array_key_exists($package, $composer['require-dev'] ?? []);
    if (! $inComposer) {
        $errors[] = "Registered package [{$package}] is not present in Composer manifests.";
    }

    foreach (['constraint', 'status', 'capability_owner', 'purpose', 'platforms'] as $key) {
        if (! array_key_exists($key, $definition)) {
            $errors[] = "Registered package [{$package}] is missing [{$key}].";
        }
    }
}

if (($registry['policies']['new_stage_errors_may_enter_phpstan_baseline'] ?? null) !== false) {
    $errors[] = 'Package registry must forbid new-stage PHPStan baseline debt.';
}

$pulse = $registered['laravel/pulse'] ?? null;
if (! is_array($pulse) || ! in_array('pgsql', $pulse['database_support'] ?? [], true)) {
    $errors[] = 'Laravel Pulse must declare PostgreSQL support.';
}

if ($errors !== []) {
    fwrite(STDERR, "Package governance verification failed:\n - ".implode("\n - ", $errors)."\n");
    exit(1);
}

echo "Package governance verification passed.\n";
