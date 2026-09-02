<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$errors = [];

$requiredFiles = [
    '.github/workflows/tests.yml',
    '.github/workflows/songchart-mobile.yml',
    'phpunit.xml',
    'composer.json',
    'package.json',
];

foreach ($requiredFiles as $file) {
    if (! is_file($root.DIRECTORY_SEPARATOR.$file)) {
        $errors[] = "Required CI file is missing: {$file}";
    }
}

$phpunit = is_file($root.'/phpunit.xml') ? file_get_contents($root.'/phpunit.xml') : '';
foreach (['tests/Unit', 'tests/Architecture', 'tests/Feature'] as $suitePath) {
    if (! is_string($phpunit) || ! str_contains($phpunit, $suitePath)) {
        $errors[] = "PHPUnit suite is not registered: {$suitePath}";
    }
}

$composer = is_file($root.'/composer.json')
    ? json_decode((string) file_get_contents($root.'/composer.json'), true)
    : null;

if (! is_array($composer)) {
    $errors[] = 'composer.json is not valid JSON.';
} else {
    $scripts = $composer['scripts'] ?? [];
    foreach (['quality:verify', 'test:unit', 'test:architecture', 'test:feature', 'test:postgres', 'ci:configuration', 'locks:verify', 'canonical:verify', 'stage:verify'] as $script) {
        if (! is_array($scripts) || ! array_key_exists($script, $scripts)) {
            $errors[] = "Composer CI script is missing: {$script}";
        }
    }
}

$workflow = is_file($root.'/.github/workflows/tests.yml')
    ? file_get_contents($root.'/.github/workflows/tests.yml')
    : '';

foreach (['quality:', 'tests-postgres:', 'frontend-build:'] as $job) {
    if (! is_string($workflow) || ! str_contains($workflow, $job)) {
        $errors[] = "GitHub Actions job is missing: {$job}";
    }
}

foreach (['composer validate --strict', 'composer quality:verify', 'composer test:postgres', 'npm ci --no-audit --no-fund', 'npm run build'] as $command) {
    if (! is_string($workflow) || ! str_contains($workflow, $command)) {
        $errors[] = "GitHub Actions command is missing: {$command}";
    }
}

$requiredWorkflowFragments = [
    "push:\n    branches:\n      - main",
    "pull_request:\n    branches:\n      - main",
    'workflow_dispatch:',
    'concurrency:',
    'group: tests-${{ github.workflow }}-${{ github.event.pull_request.number || github.ref }}',
    'cancel-in-progress: true',
    'actions/checkout@v6',
    'actions/setup-node@v6',
];

foreach ($requiredWorkflowFragments as $fragment) {
    if (! is_string($workflow) || ! str_contains($workflow, $fragment)) {
        $errors[] = 'GitHub Actions feedback-loop contract is missing: '.str_replace("\n", ' / ', $fragment);
    }
}

if (is_string($workflow) && substr_count($workflow, "node-version: '24'") < 2) {
    $errors[] = 'GitHub application quality/frontend lanes must both use Node 24 LTS.';
}
if (is_string($workflow) && str_contains($workflow, "node-version: '22'")) {
    $errors[] = 'GitHub workflow must not drift back to Node 22 after Node 24 alignment.';
}

if (is_string($workflow) && preg_match('/^\s{2}push:\s*$/m', $workflow) === 1 && ! str_contains($workflow, "push:\n    branches:\n      - main")) {
    $errors[] = 'Full CI must not run on every feature-branch push; push verification is restricted to main.';
}

$mobileWorkflow = is_file($root.'/.github/workflows/songchart-mobile.yml')
    ? file_get_contents($root.'/.github/workflows/songchart-mobile.yml')
    : '';

$requiredMobileWorkflowFragments = [
    'name: SongChart Mobile',
    'workflow_dispatch:',
    'type: choice',
    '- check',
    '- close',
    'permissions:',
    'contents: read',
    'ref: ${{ github.sha }}',
    'run: ./mobile check --verbose',
    'run: ./songchart verify',
    'test "$(git rev-parse HEAD)" = "$SONGCHART_MOBILE_SHA"',
    'test -z "$(git status --porcelain --untracked-files=no)"',
];

foreach ($requiredMobileWorkflowFragments as $fragment) {
    if (! is_string($mobileWorkflow) || ! str_contains($mobileWorkflow, $fragment)) {
        $errors[] = 'GitHub mobile control-plane contract is missing: '.$fragment;
    }
}

foreach (['./mobile close', 'git commit', 'git push', 'composer canonical:verify', 'composer stage:verify'] as $forbiddenMobileFragment) {
    if (is_string($mobileWorkflow) && str_contains($mobileWorkflow, $forbiddenMobileFragment)) {
        $errors[] = 'GitHub mobile control plane must delegate without mutating or reimplementing closure: '.$forbiddenMobileFragment;
    }
}

if ($errors !== []) {
    foreach ($errors as $error) {
        fwrite(STDERR, $error.PHP_EOL);
    }

    exit(1);
}

echo 'CI configuration verification passed.'.PHP_EOL;
