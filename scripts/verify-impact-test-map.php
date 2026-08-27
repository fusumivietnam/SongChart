<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$errors = [];

try {
    $map = readJson($root.'/docs/project/stack/impact-test-map.json');
    $composer = readJson($root.'/composer.json');
    $topology = readJson($root.'/docs/project/engineering/verification-topology.json');
} catch (Throwable $exception) {
    fwrite(STDERR, "Impact-test map verification failed:\n- {$exception->getMessage()}\n");
    exit(1);
}

if (($map['version'] ?? null) !== 2) {
    $errors[] = 'impact-test-map.json must use version 2.';
}

$composerScripts = is_array($composer['scripts'] ?? null) ? $composer['scripts'] : [];
$removedAliases = array_fill_keys(array_values(array_filter((array) ($topology['removed_aliases'] ?? []), 'is_string')), true);
$rules = is_array($map['rules'] ?? null) ? $map['rules'] : [];
$allPaths = [];

foreach ($rules as $index => $rule) {
    if (! is_array($rule) || ! is_array($rule['paths'] ?? null) || ! is_array($rule['required_tests'] ?? null)) {
        $errors[] = 'Every impact-map rule must declare paths and required_tests.';
        continue;
    }

    $ruleName = (string) ($rule['name'] ?? 'rule-'.($index + 1));
    foreach ($rule['paths'] as $pattern) {
        if (is_string($pattern) && $pattern !== '') {
            $allPaths[] = $pattern;
        }
    }

    foreach ($rule['required_tests'] as $target) {
        if (! is_string($target) || trim($target) === '') {
            $errors[] = "Impact rule [{$ruleName}] contains an empty/non-string required test.";
            continue;
        }

        $target = trim($target);
        if (preg_match('/^composer\s+([^\s]+)/', $target, $matches) === 1) {
            $script = $matches[1];
            if (isset($removedAliases[$script])) {
                $errors[] = "Impact rule [{$ruleName}] references retired Composer command [{$script}].";
            } elseif (! array_key_exists($script, $composerScripts)) {
                $errors[] = "Impact rule [{$ruleName}] references missing Composer command [{$script}].";
            }
            continue;
        }

        if (str_starts_with($target, 'tests/')) {
            $absolute = $root.'/'.$target;
            if (strpbrk($target, '*?[') !== false) {
                if (glob($absolute, GLOB_BRACE) === []) {
                    $errors[] = "Impact rule [{$ruleName}] references unmatched test glob [{$target}].";
                }
            } elseif (! is_file($absolute) && ! is_dir($absolute)) {
                $errors[] = "Impact rule [{$ruleName}] references missing test target [{$target}].";
            }
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
    fwrite(STDERR, "Impact-test map verification failed:\n- ".implode("\n- ", array_values(array_unique($errors)))."\n");
    exit(1);
}

fwrite(STDOUT, "Impact-test map verification passed.\n");

/** @return array<string, mixed> */
function readJson(string $path): array
{
    $decoded = json_decode((string) file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);
    if (! is_array($decoded)) {
        throw new RuntimeException("Expected JSON object at {$path}.");
    }
    return $decoded;
}
