<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$contract = json_decode((string) file_get_contents($root.'/docs/project/stack/candidate-verification-contract.json'), true, 512, JSON_THROW_ON_ERROR);
$manifest = json_decode((string) file_get_contents($root.'/candidate-verification.json'), true, 512, JSON_THROW_ON_ERROR);
$readme = (string) file_get_contents($root.'/README.md');

$errors = [];
$allowed = $contract['allowed_status'] ?? [];

$currentStage = null;
if (preg_match('/^Current stage:\s*\*\*([0-9]+(?:\.[0-9]+)+)\s+—/m', $readme, $matches) === 1) {
    $currentStage = $matches[1];
} else {
    $errors[] = 'Unable to resolve the current stage from README.md.';
}

if (is_string($currentStage) && ($manifest['stage'] ?? null) !== $currentStage) {
    $manifestStage = is_string($manifest['stage'] ?? null) ? $manifest['stage'] : '<missing>';
    $errors[] = "candidate-verification.json stage [{$manifestStage}] must match README current stage [{$currentStage}].";
}

foreach (($contract['required_gates'] ?? []) as $gate) {
    if (! array_key_exists($gate, $manifest['gates'] ?? [])) {
        $errors[] = "Candidate manifest is missing gate [{$gate}].";

        continue;
    }

    if (! in_array($manifest['gates'][$gate], $allowed, true)) {
        $errors[] = "Candidate gate [{$gate}] has an invalid status.";
    }
}

$allPassed = ! in_array('failed', $manifest['gates'] ?? [], true)
    && ! in_array('not_run', $manifest['gates'] ?? [], true);

if (($manifest['closure_ready'] ?? false) !== $allPassed) {
    $errors[] = 'candidate-verification.json closure_ready does not match gate statuses.';
}

if ($errors !== []) {
    fwrite(STDERR, "Candidate verification contract failed:\n - ".implode("\n - ", $errors)."\n");

    exit(1);
}

echo "Candidate verification contract passed.\n";
