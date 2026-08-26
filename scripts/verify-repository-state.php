<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$errors = [];

$read = static function (string $relative) use ($root, &$errors): string {
    $path = $root.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $relative);
    $contents = @file_get_contents($path);
    if ($contents === false) {
        $errors[] = "Missing or unreadable repository-state authority: {$relative}";

        return '';
    }

    return $contents;
};

$readme = $read('README.md');
$startHere = $read('docs/START_HERE.md');
$index = $read('docs/DOCUMENTATION_INDEX.md');
$developmentState = $read('docs/project/DEVELOPMENT_STATE.md');
$roadmap = $read('docs/project/docs/ROADMAP.md');
$releaseStatus = $read('docs/project/RELEASE_BASELINE_STATUS.md');
$stage12Manifest = $read('STAGE_12_CHANGE_MANIFEST.md');
$taskTemplate = $read('docs/templates/TASK_CONTRACT_TEMPLATE.md');

foreach (['Current stage:', 'Candidate delivery:', '## Current development stage'] as $marker) {
    if (str_contains($readme, $marker)) {
        $errors[] = "README.md must not own development progress marker [{$marker}].";
    }
}

$currentStage = null;
if (preg_match('/^- Stage\s+`([0-9]+(?:\.[0-9]+)+)\s+—\s+[^`]+`/m', $developmentState, $match) === 1) {
    $currentStage = $match[1];
} else {
    $errors[] = 'DEVELOPMENT_STATE.md must declare the current numeric stage and title.';
}

if (!str_contains($startHere, 'docs/project/DEVELOPMENT_STATE.md')) {
    $errors[] = 'docs/START_HERE.md must route current work to DEVELOPMENT_STATE.md.';
}
if (str_contains($startHere, 'Current stage:')) {
    $errors[] = 'docs/START_HERE.md must not duplicate current-stage state.';
}
if (preg_match('/^## Current stage\s*$/mi', $roadmap) === 1 || str_contains($roadmap, 'Current stage:')) {
    $errors[] = 'ROADMAP.md must be future-looking and must not own current-stage state.';
}

if (is_string($currentStage)) {
    $stageToken = str_replace('.', '_', $currentStage);
    foreach (['TASK_CONTRACT', 'VALIDATION_REPORT'] as $kind) {
        $relative = "docs/foundation/STAGE_{$stageToken}_{$kind}.md";
        if (!is_file($root.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $relative))) {
            $errors[] = "Missing current-stage governance record: {$relative}";
        }
    }
}

foreach (['## Expected files', '## Allowed incidental files', '## Scope deviations'] as $section) {
    if (!str_contains($taskTemplate, $section)) {
        $errors[] = "TASK_CONTRACT_TEMPLATE.md must include {$section}.";
    }
}
if (!str_contains($stage12Manifest, 'Status: historical manifest; frozen at Stage 16.4.4.')) {
    $errors[] = 'STAGE_12_CHANGE_MANIFEST.md must be explicitly historical and frozen.';
}

$requiredIndexFragments = [
    'project/DEVELOPMENT_STATE.md',
    'project/DEVELOPMENT_HISTORY.md',
    'project/RELEASE_BASELINE_STATUS.md',
    'project/engineering/AI_DEVELOPMENT_PROTOCOL.md',
    'project/domain/domain-contracts.json',
    'project/domain/operational-contracts.json',
    'project/domain/use-case-contracts.json',
];
foreach ($requiredIndexFragments as $fragment) {
    if (!str_contains($index, $fragment)) {
        $errors[] = "DOCUMENTATION_INDEX.md is missing repository-state entry: {$fragment}";
    }
}

foreach (['composer.lock', 'package-lock.json', 'composer canonical:verify', 'composer release:package'] as $needle) {
    if (!str_contains($releaseStatus, $needle)) {
        $errors[] = "RELEASE_BASELINE_STATUS.md is missing release invariant: {$needle}";
    }
}

try {
    $composer = json_decode((string) file_get_contents($root.'/composer.json'), true, flags: JSON_THROW_ON_ERROR);
} catch (JsonException $exception) {
    $errors[] = 'composer.json is invalid JSON: '.$exception->getMessage();
    $composer = [];
}
$scripts = is_array($composer['scripts'] ?? null) ? $composer['scripts'] : [];
$requiredScripts = [
    'repository-state:verify' => '@php scripts/verify-repository-state.php',
    'operational-contracts:verify' => '@php scripts/verify-operational-contracts.php',
    'impact-map:verify' => '@php scripts/verify-impact-test-map.php',
];
foreach ($requiredScripts as $name => $command) {
    if (($scripts[$name] ?? null) !== $command) {
        $errors[] = "composer.json must define {$name}.";
    }
}
$quality = $scripts['quality:verify'] ?? [];
foreach (['@repository-state:verify', '@operational-contracts:verify', '@impact-map:verify'] as $gate) {
    if (!is_array($quality) || !in_array($gate, $quality, true)) {
        $errors[] = "quality:verify must execute {$gate}.";
    }
}

if ($errors !== []) {
    fwrite(STDERR, "Repository-state verification failed:\n- ".implode("\n- ", array_unique($errors))."\n");
    exit(1);
}

fwrite(STDOUT, "Repository-state verification passed.\n");
