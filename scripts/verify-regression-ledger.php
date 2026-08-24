<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$errors = [];

try {
    $ledger = json_decode((string) file_get_contents($root.'/docs/project/engineering/regression-ledger.json'), true, flags: JSON_THROW_ON_ERROR);
    $composer = json_decode((string) file_get_contents($root.'/composer.json'), true, flags: JSON_THROW_ON_ERROR);
} catch (Throwable $exception) {
    fwrite(STDERR, 'Regression ledger verification failed: '.$exception->getMessage().PHP_EOL);
    exit(1);
}

$knownComposerGuards = array_keys(is_array($composer['scripts'] ?? null) ? $composer['scripts'] : []);
$virtualGuards = ['phpstan', 'release:package'];
$ids = [];
foreach (($ledger['regressions'] ?? []) as $entry) {
    if (! is_array($entry)) {
        $errors[] = 'Regression entries must be objects.';

        continue;
    }

    $id = (string) ($entry['id'] ?? '');
    if ($id === '' || isset($ids[$id])) {
        $errors[] = "Regression id is missing or duplicated [{$id}].";
    }
    $ids[$id] = true;

    if (($entry['status'] ?? null) !== 'guarded') {
        $errors[] = "Regression {$id} is not guarded.";
    }

    $guards = $entry['permanent_guards'] ?? [];
    if (! is_array($guards) || $guards === []) {
        $errors[] = "Regression {$id} has no permanent machine guard.";

        continue;
    }

    foreach ($guards as $guard) {
        if (! is_string($guard)) {
            $errors[] = "Regression {$id} has an invalid guard.";

            continue;
        }

        $composerName = str_ends_with($guard, ':verify')
            ? substr($guard, 0, -strlen(':verify')).':verify'
            : $guard;

        if (! in_array($guard, $virtualGuards, true) && ! in_array($composerName, $knownComposerGuards, true)) {
            $errors[] = "Regression {$id} references unknown guard [{$guard}].";
        }
    }
}

if (($ledger['policy']['phpstan_baseline_widening_allowed'] ?? true) !== false) {
    $errors[] = 'Regression policy must prohibit widening the PHPStan baseline for new errors.';
}

if ($errors !== []) {
    fwrite(STDERR, "Regression ledger verification failed:\n- ".implode("\n- ", array_unique($errors)).PHP_EOL);
    exit(1);
}

fwrite(STDOUT, 'Regression ledger verification passed: '.count($ids).' historical failure classes are guarded.'.PHP_EOL);
