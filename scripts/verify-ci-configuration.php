<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$errors = [];

$requiredFiles = [
    '.github/workflows/tests.yml',
    '.github/workflows/auto-closure.yml',
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

/**
 * @param  list<string>  $actions
 */
$verifyImmutableActions = static function (string $source, array $actions, string $surface) use (&$errors): void {
    foreach ($actions as $action) {
        $pattern = '/uses:\s*'.preg_quote($action, '/').'@([0-9a-f]{40})\b/m';
        if (preg_match($pattern, $source) !== 1) {
            $errors[] = "{$surface} action must be pinned by immutable commit SHA: {$action}.";
        }

        if (preg_match('/uses:\s*'.preg_quote($action, '/').'@v\d+/m', $source) === 1) {
            $errors[] = "{$surface} action must not use a rolling major tag: {$action}.";
        }
    }
};

$workflow = is_file($root.'/.github/workflows/tests.yml')
    ? file_get_contents($root.'/.github/workflows/tests.yml')
    : '';

foreach (['quality:', 'tests-postgres:', 'frontend-build:', 'classify:'] as $job) {
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
    'workflow_dispatch:',
    'workflow_call:',
    'target_sha:',
    'SONGCHART_CI_SHA: ${{ inputs.target_sha || github.sha }}',
    "permissions:\n  contents: read",
    'concurrency:',
    'cancel-in-progress: true',
    'ci-failure-classification.json',
    'quality-governance',
    'database-runtime',
    'frontend-build',
    'accepted-main-provenance.json',
    'accepted-main-provenance-${{ env.SONGCHART_CI_SHA }}',
];

foreach ($requiredWorkflowFragments as $fragment) {
    if (! is_string($workflow) || ! str_contains($workflow, $fragment)) {
        $errors[] = 'GitHub Actions feedback-loop contract is missing: '.str_replace("\n", ' / ', $fragment);
    }
}

if (is_string($workflow)) {
    $verifyImmutableActions($workflow, [
        'actions/checkout',
        'actions/setup-node',
        'actions/cache',
        'actions/upload-artifact',
        'shivammathur/setup-php',
    ], 'Reusable tests workflow');
}

if (is_string($workflow) && str_contains($workflow, "pull_request:\n    branches:\n      - main")) {
    $errors[] = 'Reusable tests workflow must not duplicate PR verification owned by auto closure.';
}
if (is_string($workflow) && substr_count($workflow, "node-version: '24'") < 2) {
    $errors[] = 'GitHub application browser/frontend lanes must both use Node 24 LTS.';
}
if (is_string($workflow) && str_contains($workflow, "node-version: '22'")) {
    $errors[] = 'GitHub workflow must not drift back to Node 22 after Node 24 alignment.';
}
if (is_string($workflow) && substr_count($workflow, 'persist-credentials: false') < 4) {
    $errors[] = 'Reusable tests workflow checkouts must not persist GitHub credentials.';
}
if (is_string($workflow) && substr_count($workflow, 'timeout-minutes:') < 5) {
    $errors[] = 'Reusable tests workflow jobs must keep bounded execution timeouts.';
}

$autoClosure = is_file($root.'/.github/workflows/auto-closure.yml')
    ? file_get_contents($root.'/.github/workflows/auto-closure.yml')
    : '';

$requiredAutoClosureFragments = [
    'name: SongChart Auto Closure',
    'pull_request:',
    "permissions:\n  contents: read",
    "prepare:\n    if:",
    'contents: write',
    'pull-requests: read',
    'github.event.pull_request.head.repo.full_name == github.repository',
    'persist-credentials: false',
    'php scripts/project-context.php --write-source',
    'php scripts/compile-repository-contracts.php --refresh-check',
    'grep -vE \'^.. docs/project/generated(/|$)\'',
    'git add docs/project/generated',
    'commit -m \'chore: refresh generated repository authority\'',
    'uses: ./.github/workflows/tests.yml',
    'target_sha: ${{ needs.prepare.outputs.effective_sha }}',
    'run: ./songchart verify',
    'test -z "$(git status --porcelain --untracked-files=no)"',
    'Capture PR promotion state',
    'PR state: draft; promote via GitHub UI or an authorized connector',
    'Any new commit invalidates this evidence and restarts auto closure',
];

foreach ($requiredAutoClosureFragments as $fragment) {
    if (! is_string($autoClosure) || ! str_contains($autoClosure, $fragment)) {
        $errors[] = 'GitHub auto-closure contract is missing: '.$fragment;
    }
}

if (is_string($autoClosure)) {
    $verifyImmutableActions($autoClosure, [
        'actions/checkout',
        'actions/cache',
        'shivammathur/setup-php',
    ], 'Auto Closure');
}

if (is_string($autoClosure) && preg_match('/^permissions:\s*\n\s*contents:\s*write/m', $autoClosure) === 1) {
    $errors[] = 'Auto Closure must not grant contents:write at workflow scope; write permission belongs only to PREPARE.';
}
if (is_string($autoClosure) && str_contains($autoClosure, 'pull-requests: write')) {
    $errors[] = 'Auto Closure promotion handoff must remain read-only; CI must not mutate PR UI state.';
}
if (is_string($autoClosure) && substr_count($autoClosure, 'timeout-minutes:') < 3) {
    $errors[] = 'Auto Closure execution jobs must keep bounded timeouts.';
}

foreach (['./mobile close', 'composer canonical:verify', 'composer stage:verify', 'markPullRequestReadyForReview', 'gh api graphql'] as $forbiddenAutoClosureFragment) {
    if (is_string($autoClosure) && str_contains($autoClosure, $forbiddenAutoClosureFragment)) {
        $errors[] = 'GitHub auto closure must delegate without reimplementing closure or PR mutation: '.$forbiddenAutoClosureFragment;
    }
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
    'SONGCHART_TARGET_SHA: ${{ github.sha }}',
    'ref: ${{ env.SONGCHART_TARGET_SHA }}',
    'run: ./mobile check --verbose',
    'run: ./songchart verify',
    'test "$(git rev-parse HEAD)" = "$SONGCHART_MOBILE_SHA"',
    'test -z "$(git status --porcelain --untracked-files=no)"',
    'Manual fallback only; normal PR closure is owned by SongChart Auto Closure.',
];

foreach ($requiredMobileWorkflowFragments as $fragment) {
    if (! is_string($mobileWorkflow) || ! str_contains($mobileWorkflow, $fragment)) {
        $errors[] = 'GitHub mobile control-plane contract is missing: '.$fragment;
    }
}

if (is_string($mobileWorkflow)) {
    $verifyImmutableActions($mobileWorkflow, ['actions/checkout'], 'Mobile fallback');
}

foreach (['pull_request:', './mobile close', 'git commit', 'git push', 'composer canonical:verify', 'composer stage:verify'] as $forbiddenMobileFragment) {
    if (is_string($mobileWorkflow) && str_contains($mobileWorkflow, $forbiddenMobileFragment)) {
        $errors[] = 'GitHub mobile fallback must remain read-only and manual: '.$forbiddenMobileFragment;
    }
}

foreach (glob($root.'/tests/Architecture/*.php') ?: [] as $testPath) {
    $testSource = (string) file_get_contents($testPath);
    if (preg_match('/(?:actions\/[a-z0-9_.-]+|shivammathur\/setup-php)@[0-9a-f]{40}\b/i', $testSource) === 1) {
        $errors[] = 'Architecture tests must verify immutable action-pin shape/behavior instead of duplicating volatile external action SHAs: '.basename($testPath);
    }
}

if ($errors !== []) {
    foreach ($errors as $error) {
        fwrite(STDERR, $error.PHP_EOL);
    }

    exit(1);
}

echo 'CI configuration verification passed.'.PHP_EOL;
