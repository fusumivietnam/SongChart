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
$hash = static function (string $relative) use ($root): ?string {
    $path = $root.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $relative);
    if (! is_file($path)) {
        return null;
    }
    $content = str_replace(["\r\n", "\r"], "\n", (string) file_get_contents($path));

    return hash('sha256', $content);
};

$readme = $read('README.md');
$startHere = $read('docs/START_HERE.md');
$index = $read('docs/DOCUMENTATION_INDEX.md');
$developmentPointer = $read('docs/project/DEVELOPMENT_STATE.md');
$generatedStateJson = $read('docs/project/generated/development-state.json');
$generatedStateMarkdown = $read('docs/project/generated/DEVELOPMENT_STATE.md');
$stagePlanJson = $read('docs/project/engineering/stage-plan.json');
$consolidationJson = $read('docs/project/engineering/documentation-consolidation-contract.json');
$recheckPolicyJson = $read('docs/project/engineering/recheck-policy.json');
$roadmap = $read('docs/project/docs/ROADMAP.md');
$releaseStatus = $read('docs/project/RELEASE_BASELINE_STATUS.md');
$taskTemplate = $read('docs/templates/TASK_CONTRACT_TEMPLATE.md');

foreach (['Current stage:', 'Candidate delivery:', '## Current development stage'] as $marker) {
    if (str_contains($readme, $marker)) {
        $errors[] = "README.md must not own development progress marker [{$marker}].";
    }
}

$currentStage = null;
$currentTaskContract = null;
try {
    $state = json_decode($generatedStateJson, true, flags: JSON_THROW_ON_ERROR);
    $currentStageNode = is_array($state['current_stage'] ?? null) ? $state['current_stage'] : [];
    $currentStage = $currentStageNode['id'] ?? null;
    $currentTaskContract = $currentStageNode['task_contract'] ?? null;
    if (! is_string($currentStage) || preg_match('/^[0-9]+(?:\.[0-9]+)+$/', $currentStage) !== 1) {
        $errors[] = 'Generated development-state.json must declare current_stage.id.';
        $currentStage = null;
    }
    if (! is_string($currentTaskContract) || $currentTaskContract === '') {
        $errors[] = 'Generated development-state.json must declare current_stage.task_contract.';
        $currentTaskContract = null;
    }
    if (($state['generated_from_repository'] ?? false) !== true) {
        $errors[] = 'Generated development-state.json must be repository-derived.';
    }
    if (! is_string($state['source_fingerprint'] ?? null) || $state['source_fingerprint'] === '') {
        $errors[] = 'Generated development-state.json must contain a source fingerprint.';
    }
    $sourceHashes = is_array($state['source_hashes'] ?? null) ? $state['source_hashes'] : [];
    $requiredStateSources = [
        'docs/project/engineering/stage-plan.json',
        'docs/project/engineering/project-knowledge.json',
        'docs/project/engineering/consolidation-plan.json',
        'docs/project/engineering/roadmap.json',
        'docs/project/governance/project-control-plane.json',
        'docs/project/governance/technology-lifecycle.json',
        'docs/project/governance/pre-data-freeze.json',
        'docs/project/governance/resilience-matrix.json',
        'docs/project/governance/database-risk-register.json',
        'docs/project/governance/deletion-retention-matrix.json',
        'candidate-verification.json',
    ];
    foreach ($requiredStateSources as $relative) {
        if (($sourceHashes[$relative] ?? null) !== $hash($relative)) {
            $errors[] = "Generated development state is stale for [{$relative}]. Auto Closure PREPARE must regenerate it.";
        }
    }
    foreach (array_keys($sourceHashes) as $relative) {
        if (! in_array($relative, $requiredStateSources, true)) {
            $errors[] = "Generated development state contains obsolete source [{$relative}].";
        }
    }
} catch (Throwable $exception) {
    $errors[] = 'Unable to decode generated development-state.json: '.$exception->getMessage();
}

try {
    $stagePlan = json_decode($stagePlanJson, true, flags: JSON_THROW_ON_ERROR);
    $authoredStage = is_array($stagePlan['current_stage'] ?? null) ? $stagePlan['current_stage'] : [];
    if (is_string($currentStage) && ($authoredStage['id'] ?? null) !== $currentStage) {
        $errors[] = 'Generated current stage must match docs/project/engineering/stage-plan.json.';
    }
    if (is_string($currentTaskContract) && ($authoredStage['task_contract'] ?? null) !== $currentTaskContract) {
        $errors[] = 'Generated current-stage task contract must match docs/project/engineering/stage-plan.json.';
    }
} catch (Throwable $exception) {
    $errors[] = 'Unable to decode stage-plan.json: '.$exception->getMessage();
}

if (is_string($currentTaskContract)) {
    $taskPath = $root.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $currentTaskContract);
    if (! is_file($taskPath)) {
        $errors[] = "Missing current-stage task contract declared by stage authority: {$currentTaskContract}";
    }
}

try {
    $consolidation = json_decode($consolidationJson, true, flags: JSON_THROW_ON_ERROR);
    if (($consolidation['status'] ?? null) !== 'active') {
        $errors[] = 'Documentation consolidation contract must remain active while legacy stage records are being retired.';
    }
    if (($consolidation['target_owners']['exact_historical_evidence'] ?? null) !== 'Git commit, pull-request and release history') {
        $errors[] = 'Documentation consolidation must keep Git/PR/release history as exact retired-stage evidence authority.';
    }
} catch (Throwable $exception) {
    $errors[] = 'Unable to decode documentation-consolidation-contract.json: '.$exception->getMessage();
}

try {
    $recheckPolicy = json_decode($recheckPolicyJson, true, flags: JSON_THROW_ON_ERROR);
    if (($recheckPolicy['status'] ?? null) !== 'active') {
        $errors[] = 'Recheck policy must be active.';
    }
    foreach (['session_resume_or_new_ai', 'authored_authority_change', 'before_tranche_advance', 'before_candidate_or_canonical_closure', 'external_integration_change'] as $trigger) {
        if (! is_array($recheckPolicy['triggers'][$trigger] ?? null)) {
            $errors[] = "Recheck policy is missing trigger [{$trigger}].";
        }
    }
} catch (Throwable $exception) {
    $errors[] = 'Unable to decode recheck-policy.json: '.$exception->getMessage();
}

foreach (['compatibility pointer', 'docs/project/generated/development-state.json', './songchart ai status --json'] as $signal) {
    if (! str_contains($developmentPointer, $signal)) {
        $errors[] = "DEVELOPMENT_STATE.md compatibility pointer is missing [{$signal}].";
    }
}
if (! str_contains($generatedStateMarkdown, 'Generated from repository machine authorities')) {
    $errors[] = 'Generated DEVELOPMENT_STATE.md must identify itself as generated.';
}

if (! str_contains($startHere, 'docs/project/DEVELOPMENT_STATE.md')) {
    $errors[] = 'docs/START_HERE.md must route current work through the DEVELOPMENT_STATE compatibility pointer.';
}
if (str_contains($startHere, 'Current stage:')) {
    $errors[] = 'docs/START_HERE.md must not duplicate current-stage state.';
}
if (preg_match('/^## Current stage\s*$/mi', $roadmap) === 1 || str_contains($roadmap, 'Current stage:')) {
    $errors[] = 'ROADMAP.md must be future-looking and must not own current-stage state.';
}

foreach (['## Expected files', '## Allowed incidental files', '## Post-diff impact and scope deviations'] as $section) {
    if (! str_contains($taskTemplate, $section)) {
        $errors[] = "TASK_CONTRACT_TEMPLATE.md must include {$section}.";
    }
}

$requiredIndexFragments = [
    'project/DEVELOPMENT_STATE.md',
    'project/DEVELOPMENT_HISTORY.md',
    'project/RELEASE_BASELINE_STATUS.md',
    'project/engineering/recheck-policy.json',
    'project/engineering/AI_DEVELOPMENT_PROTOCOL.md',
    'project/domain/domain-contracts.json',
    'project/domain/operational-contracts.json',
    'project/domain/use-case-contracts.json',
];
foreach ($requiredIndexFragments as $fragment) {
    if (! str_contains($index, $fragment)) {
        $errors[] = "DOCUMENTATION_INDEX.md is missing repository-state entry: {$fragment}";
    }
}

foreach (['composer.lock', 'package-lock.json', 'composer canonical:verify', 'composer release:package'] as $needle) {
    if (! str_contains($releaseStatus, $needle)) {
        $errors[] = "RELEASE_BASELINE_STATUS.md is missing release invariant: {$needle}.";
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
    if (! is_array($quality) || ! in_array($gate, $quality, true)) {
        $errors[] = "quality:verify must execute {$gate}.";
    }
}

if ($errors !== []) {
    fwrite(STDERR, "Repository-state verification failed:\n- ".implode("\n- ", array_unique($errors))."\n");
    exit(1);
}

fwrite(STDOUT, "Repository-state verification passed.\n");
