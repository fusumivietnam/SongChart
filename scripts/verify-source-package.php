<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$deliveryMode = in_array('--delivery', $argv, true);
$errors = [];

$forbidden = $deliveryMode
    ? ['payload', '.changeset-backups', 'vendor', 'node_modules']
    : ['payload'];

$forbiddenFiles = $deliveryMode ? ['.env', 'public/hot'] : [];

foreach ($forbidden as $relative) {
    if (file_exists($root.DIRECTORY_SEPARATOR.$relative)) {
        $scope = $deliveryMode ? 'Delivery package' : 'Working tree';
        $errors[] = "{$scope} must not contain artefact: {$relative}";
    }
}
foreach ($forbiddenFiles as $relative) {
    if (is_file($root.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $relative))) {
        $errors[] = "Delivery package must not contain local/runtime file: {$relative}";
    }
}

if ($deliveryMode) {
    $required = [
        'README.md',
        '.env.example',
        'composer.json',
        'composer.lock',
        'package.json',
        'package-lock.json',
        'artisan',
        'bootstrap/app.php',
        'routes/web.php',
        'docs/DOCUMENTATION_INDEX.md',
        'docs/project/DEVELOPMENT_HISTORY.md',
        'docs/project/docs/ROADMAP.md',
        'docs/project/domain/domain-contracts.json',
        'docs/project/domain/operational-contracts.json',
        'docs/project/domain/use-case-contracts.json',
    ];
    foreach ($required as $relative) {
        if (! is_file($root.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $relative))) {
            $errors[] = "Delivery package is missing mandatory file: {$relative}";
        }
    }

    $readme = @file_get_contents($root.'/README.md');
    if ($readme === false || preg_match('/^Current stage:\s*\*\*([0-9]+(?:\.[0-9]+)+)\s+—\s+([^*]+)\*\*$/m', $readme, $match) !== 1) {
        $errors[] = 'Delivery package README must declare exactly one valid Current stage pointer.';
    } else {
        $stage = $match[1];
        $title = trim($match[2]);
        $token = str_replace('.', '_', $stage);
        foreach (["docs/foundation/STAGE_{$token}_TASK_CONTRACT.md", "docs/foundation/STAGE_{$token}_VALIDATION_REPORT.md"] as $relative) {
            if (! is_file($root.'/'.$relative)) {
                $errors[] = "Delivery package is missing current-stage governance file: {$relative}";
            }
        }
        $history = (string) @file_get_contents($root.'/docs/project/DEVELOPMENT_HISTORY.md');
        if (! str_contains($history, "| {$stage} | {$title} |")) {
            $errors[] = 'Delivery package current stage does not match DEVELOPMENT_HISTORY.md.';
        }
    }
}

if ($errors !== []) {
    fwrite(STDERR, implode(PHP_EOL, array_unique($errors)).PHP_EOL);
    exit(1);
}

$mode = $deliveryMode ? 'delivery package' : 'working tree';
echo "Source verification passed for {$mode}.".PHP_EOL;
