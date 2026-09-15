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
        'docs/project/engineering/stage-plan.json',
        'docs/project/domain/domain-contracts.json',
        'docs/project/domain/operational-contracts.json',
        'docs/project/domain/use-case-contracts.json',
    ];
    foreach ($required as $relative) {
        if (! is_file($root.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $relative))) {
            $errors[] = "Delivery package is missing mandatory file: {$relative}";
        }
    }

    $stagePlanPath = $root.'/docs/project/engineering/stage-plan.json';
    $stagePlan = is_file($stagePlanPath) ? json_decode((string) file_get_contents($stagePlanPath), true) : null;
    if (! is_array($stagePlan)) {
        $errors[] = 'Delivery package stage-plan.json must be valid JSON.';
    } else {
        $current = is_array($stagePlan['current_stage'] ?? null) ? $stagePlan['current_stage'] : [];
        $stage = is_string($current['id'] ?? null) ? $current['id'] : '';
        $title = is_string($current['title'] ?? null) ? trim($current['title']) : '';
        $taskContract = is_string($current['task_contract'] ?? null) ? $current['task_contract'] : '';

        if ($stage === '' || $title === '' || $taskContract === '') {
            $errors[] = 'Delivery package stage plan must declare current stage id, title and task_contract.';
        } elseif (! is_file($root.'/'.$taskContract)) {
            $errors[] = "Delivery package is missing current task contract declared by stage-plan.json: {$taskContract}";
        }

        $readme = (string) @file_get_contents($root.'/README.md');
        if ($stage !== '' && ! str_contains($readme, "Current stage: **{$stage} — {$title}**")) {
            $errors[] = 'Delivery package README current-stage pointer must agree with stage-plan.json.';
        }

        $acceptedThrough = is_string($stagePlan['accepted_through'] ?? null) ? $stagePlan['accepted_through'] : '';
        $history = (string) @file_get_contents($root.'/docs/project/DEVELOPMENT_HISTORY.md');
        if ($acceptedThrough !== '' && ! str_contains($history, "| {$acceptedThrough} |")) {
            $errors[] = 'Delivery package accepted_through stage must exist in DEVELOPMENT_HISTORY.md.';
        }
    }
}

if ($errors !== []) {
    fwrite(STDERR, implode(PHP_EOL, array_unique($errors)).PHP_EOL);
    exit(1);
}

$mode = $deliveryMode ? 'delivery package' : 'working tree';
echo "Source verification passed for {$mode}.".PHP_EOL;
