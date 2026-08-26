<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$contract = json_decode((string) file_get_contents($root.'/docs/project/stack/candidate-verification-contract.json'), true, 512, JSON_THROW_ON_ERROR);
$definition = json_decode((string) file_get_contents($root.'/candidate-verification.json'), true, 512, JSON_THROW_ON_ERROR);
$manifest = $definition;
$runtimePath = $root.'/storage/framework/candidate-verification-runtime.json';
$head = trim((string) shell_exec('git -C '.escapeshellarg($root).' rev-parse HEAD 2>/dev/null'));

if (is_file($runtimePath)) {
    $runtime = json_decode((string) file_get_contents($runtimePath), true, 512, JSON_THROW_ON_ERROR);
    $runtimeCommit = $runtime['evidence']['repository_contracts']['git_commit'] ?? null;
    $runtimeDirty = $runtime['evidence']['repository_contracts']['git_dirty'] ?? null;

    if (($runtime['stage'] ?? null) === ($definition['stage'] ?? null)
        && ($runtime['candidate'] ?? null) === ($definition['candidate'] ?? null)
        && is_string($runtimeCommit)
        && $runtimeCommit === $head
        && $runtimeDirty === null) {
        $manifest = $runtime;
    }
}

$developmentState = (string) file_get_contents($root.'/docs/project/DEVELOPMENT_STATE.md');
$errors = [];
$allowed = $contract['allowed_status'] ?? [];

$currentStage = null;
if (preg_match('/^- Stage\s+`([0-9]+(?:\.[0-9]+)+)\s+—/m', $developmentState, $matches) === 1) {
    $currentStage = $matches[1];
} else {
    $errors[] = 'Unable to resolve the current stage from DEVELOPMENT_STATE.md.';
}

if (is_string($currentStage) && ($definition['stage'] ?? null) !== $currentStage) {
    $definitionStage = is_string($definition['stage'] ?? null) ? $definition['stage'] : '<missing>';
    $errors[] = "candidate-verification.json stage [{$definitionStage}] must match DEVELOPMENT_STATE.md current stage [{$currentStage}].";
}

foreach (($contract['required_gates'] ?? []) as $gate) {
    if (! array_key_exists($gate, $definition['gates'] ?? [])) {
        $errors[] = "Candidate definition is missing gate [{$gate}].";

        continue;
    }

    if (! array_key_exists($gate, $manifest['gates'] ?? [])) {
        $errors[] = "Candidate evidence is missing gate [{$gate}].";

        continue;
    }

    if (! in_array($manifest['gates'][$gate], $allowed, true)) {
        $errors[] = "Candidate gate [{$gate}] has an invalid status.";
    }
}

$allPassed = ! in_array('failed', $manifest['gates'] ?? [], true)
    && ! in_array('not_run', $manifest['gates'] ?? [], true);

if (($manifest['closure_ready'] ?? false) !== $allPassed) {
    $errors[] = 'Candidate closure_ready does not match gate statuses.';
}

if ($errors !== []) {
    fwrite(STDERR, "Candidate verification contract failed:\n - ".implode("\n - ", $errors)."\n");

    exit(1);
}

echo "Candidate verification contract passed.\n";
