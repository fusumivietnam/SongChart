<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$registryPath = $root.'/docs/project/governance/authority-dependencies.json';
$errors = [];

if (! is_file($registryPath)) {
    fwrite(STDERR, "Authority dependency registry is missing.\n");
    exit(1);
}

/** @var array{version:int, authorities:array<string, array{state:string, authority_files:list<string>, dependents:list<string>, scan_paths:list<string>, deprecated_patterns:list<array{regex:string,message:string}>}>} $registry */
$registry = json_decode((string) file_get_contents($registryPath), true, 512, JSON_THROW_ON_ERROR);

/** @return list<string> */
$expand = static function (string $pattern) use ($root): array {
    $absolute = $root.'/'.$pattern;
    if (! strpbrk($pattern, '*?[')) {
        return is_file($absolute) ? [$absolute] : [];
    }

    $matches = glob($absolute) ?: [];

    return array_values(array_filter($matches, 'is_file'));
};

$relative = static fn (string $absolute): string => str_replace('\\', '/', substr($absolute, strlen($root) + 1));

$section = static function (string $source, string $heading): string {
    $pattern = '/^'.preg_quote($heading, '/').'\R(.*?)(?=^##\s|\z)/ms';

    return preg_match($pattern, $source, $match) === 1 ? trim($match[1]) : '';
};

/** @return list<string> */
$backtickValues = static function (string $source): array {
    preg_match_all('/`([^`]+)`/', $source, $matches);

    return array_values(array_unique($matches[1]));
};

foreach ($registry['authorities'] as $name => $authority) {
    foreach ([...$authority['authority_files'], ...$authority['dependents']] as $path) {
        if (! is_file($root.'/'.$path)) {
            $errors[] = "{$name}: registered file is missing: {$path}";
        }
    }

    /** @var array<string, true> $files */
    $files = [];
    foreach ($authority['scan_paths'] as $pattern) {
        foreach ($expand($pattern) as $file) {
            $files[$file] = true;
        }
    }

    foreach (array_keys($files) as $file) {
        $source = (string) file_get_contents($file);
        foreach ($authority['deprecated_patterns'] as $deprecated) {
            $pattern = '~'.$deprecated['regex'].'~i';
            $matched = @preg_match($pattern, $source);
            if ($matched === false) {
                $errors[] = "{$name}: invalid deprecated regex {$deprecated['regex']}";

                continue;
            }
            if ($matched === 1) {
                $errors[] = "{$name}: {$relative($file)} contains a superseded invariant. {$deprecated['message']}";
            }
        }
    }
}

$derivedStatePath = $root.'/docs/project/generated/development-state.json';
if (! is_file($derivedStatePath)) {
    $errors[] = 'Unable to resolve current stage for authority closure: generated development state is missing.';
} else {
    try {
        /** @var array<string, mixed> $derivedState */
        $derivedState = json_decode((string) file_get_contents($derivedStatePath), true, flags: JSON_THROW_ON_ERROR);
    } catch (JsonException $exception) {
        $errors[] = 'Unable to resolve current stage for authority closure: generated development state is invalid JSON: '.$exception->getMessage();
        $derivedState = [];
    }

    $stage = $derivedState['current_stage']['id'] ?? null;
    $taskRelative = $derivedState['current_stage']['task_contract'] ?? null;

    if (($derivedState['generated_from_repository'] ?? false) !== true
        || ! is_string($stage)
        || $stage === '') {
        $errors[] = 'Unable to resolve current stage for authority closure from generated development state.';
    } elseif (! is_string($taskRelative) || $taskRelative === '') {
        $errors[] = 'Generated development state does not resolve a current-stage task contract for authority closure.';
    } else {
        $expectedTask = 'docs/foundation/STAGE_'.str_replace('.', '_', $stage).'_TASK_CONTRACT.md';
        if ($taskRelative !== $expectedTask) {
            $errors[] = "Generated authority-closure task contract [{$taskRelative}] does not match stage [{$stage}].";
        }

        $task = $root.'/'.$taskRelative;
        if (! is_file($task)) {
            $errors[] = 'Current-stage task contract is missing for authority closure.';
        } else {
            $taskSource = (string) file_get_contents($task);
            $changedAuthorities = $backtickValues($section($taskSource, '## Changed authorities'));
            $declaredFiles = $backtickValues(
                $section($taskSource, '## Expected files')."\n".$section($taskSource, '## Allowed incidental files'),
            );
            $declared = array_fill_keys($declaredFiles, true);

            foreach ($changedAuthorities as $authorityName) {
                $authority = $registry['authorities'][$authorityName] ?? null;
                if ($authority === null) {
                    $errors[] = "Task contract names unknown authority: {$authorityName}";

                    continue;
                }

                foreach ([...$authority['authority_files'], ...$authority['dependents']] as $dependent) {
                    if (! isset($declared[$dependent])) {
                        $errors[] = "{$authorityName}: current task contract must declare dependent {$dependent}.";
                    }
                }
            }
        }
    }
}

if ($errors !== []) {
    fwrite(STDERR, "Authority dependency closure verification failed:\n- ".implode("\n- ", array_values(array_unique($errors)))."\n");
    exit(1);
}

fwrite(STDOUT, "Authority dependency closure verification passed.\n");
