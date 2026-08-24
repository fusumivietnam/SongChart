<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$errors = [];
$path = $root.'/docs/project/stack/impact-test-map.json';
try {
    $map = json_decode((string) file_get_contents($path), true, flags: JSON_THROW_ON_ERROR);
} catch (Throwable $exception) {
    fwrite(STDERR, "Impact-test map verification failed:\n- {$exception->getMessage()}\n");
    exit(1);
}

if (! is_array($map) || ($map['version'] ?? null) !== 2) {
    $errors[] = 'impact-test-map.json must use version 2.';
}

$rules = is_array($map['rules'] ?? null) ? $map['rules'] : [];
$allPaths = [];
foreach ($rules as $rule) {
    if (! is_array($rule) || ! is_array($rule['paths'] ?? null) || ! is_array($rule['required_tests'] ?? null)) {
        $errors[] = 'Every impact-map rule must declare paths and required_tests.';

        continue;
    }
    foreach ($rule['paths'] as $pattern) {
        if (is_string($pattern)) {
            $allPaths[] = $pattern;
        }
    }
}

$requiredPatterns = [
    'app/Extensions/**',
    'app/Actions/Admin/Extensions/**',
    'app/Actions/Providers/**',
    'app/Models/Providers/**',
    'app/Support/DomainContracts/**',
    'docs/project/domain/**',
    'scripts/verify-use-case-contracts.php',
    'scripts/verify-operational-contracts.php',
];
foreach ($requiredPatterns as $pattern) {
    if (! in_array($pattern, $allPaths, true)) {
        $errors[] = "impact-test-map.json is missing required path pattern: {$pattern}";
    }
}

foreach (['app/Support/Extensions/**', 'app/Actions/Extensions/**'] as $stale) {
    if (in_array($stale, $allPaths, true)) {
        $errors[] = "impact-test-map.json contains stale path pattern: {$stale}";
    }
}

if ($errors !== []) {
    fwrite(STDERR, "Impact-test map verification failed:\n- ".implode("\n- ", array_unique($errors))."\n");
    exit(1);
}

fwrite(STDOUT, "Impact-test map verification passed.\n");
