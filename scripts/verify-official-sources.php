<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$errors = [];

$requiredFiles = [
    'AGENTS.md',
    'docs/project/docs/OFFICIAL_SOURCE_POLICY.md',
    'docs/templates/TASK_CONTRACT_TEMPLATE.md',
    'docs/foundation/STAGE_15_4_TASK_CONTRACT.md',
    'docs/foundation/STAGE_15_4_VALIDATION_REPORT.md',
];

foreach ($requiredFiles as $relativePath) {
    if (! is_file($root.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $relativePath))) {
        $errors[] = "Missing official-source governance file: {$relativePath}";
    }
}

$requiredPolicySections = [
    '## Authority hierarchy',
    '## Version matching',
    '## Native capability assessment',
    '## Custom implementation threshold',
    '## Provider rule',
    '## Evidence and citations',
    '## Freshness',
];

$policy = @file_get_contents($root.'/docs/project/docs/OFFICIAL_SOURCE_POLICY.md');
if ($policy === false) {
    $errors[] = 'Unable to read the official-source policy.';
} else {
    foreach ($requiredPolicySections as $section) {
        if (! str_contains($policy, $section)) {
            $errors[] = "Official-source policy is missing required section: {$section}";
        }
    }
}

$requiredTemplateSections = [
    '## Authority and official sources',
    '### Repository authorities',
    '### Installed versions',
    '### Official external sources',
    '### Native capability assessment',
    '### Custom implementation justification',
    '## Tests and verification',
];

$template = @file_get_contents($root.'/docs/templates/TASK_CONTRACT_TEMPLATE.md');
if ($template === false) {
    $errors[] = 'Unable to read the task-contract template.';
} else {
    foreach ($requiredTemplateSections as $section) {
        if (! str_contains($template, $section)) {
            $errors[] = "Task-contract template is missing required section: {$section}";
        }
    }
}

$agents = @file_get_contents($root.'/AGENTS.md');
if ($agents === false || ! str_contains($agents, 'docs/project/docs/OFFICIAL_SOURCE_POLICY.md')) {
    $errors[] = 'AGENTS.md must require the official-source policy.';
}

$duplicateAgentPath = $root.'/docs/project/AGENTS.md';
$duplicateAgent = @file_get_contents($duplicateAgentPath);
if ($duplicateAgent === false) {
    $errors[] = 'Missing docs/project/AGENTS.md pointer.';
} else {
    if (! str_contains($duplicateAgent, 'Status: non-authoritative pointer.')) {
        $errors[] = 'docs/project/AGENTS.md must be marked as a non-authoritative pointer.';
    }

    if (substr_count($duplicateAgent, '# AI Agent Operating Contract') > 0
        || str_contains($duplicateAgent, '## Nguyên tắc bắt buộc')) {
        $errors[] = 'docs/project/AGENTS.md must not duplicate agent operating rules.';
    }
}

$derivedStatePath = $root.'/docs/project/generated/development-state.json';
if (! is_file($derivedStatePath)) {
    $errors[] = 'Unable to resolve the current stage: generated development state is missing.';
} else {
    try {
        /** @var array<string, mixed> $derivedState */
        $derivedState = json_decode((string) file_get_contents($derivedStatePath), true, flags: JSON_THROW_ON_ERROR);
    } catch (JsonException $exception) {
        $errors[] = 'Generated development state is invalid JSON: '.$exception->getMessage();
        $derivedState = [];
    }

    $stageId = $derivedState['current_stage']['id'] ?? null;
    $taskContractRelative = $derivedState['current_stage']['task_contract'] ?? null;

    if (($derivedState['generated_from_repository'] ?? false) !== true
        || ! is_string($stageId)
        || $stageId === '') {
        $errors[] = 'Unable to resolve the current stage from generated development state.';
    } elseif (! is_string($taskContractRelative) || $taskContractRelative === '') {
        $errors[] = 'Generated development state must resolve the current-stage task contract.';
    } else {
        $expectedTaskContract = 'docs/foundation/STAGE_'.str_replace('.', '_', $stageId).'_TASK_CONTRACT.md';
        if ($taskContractRelative !== $expectedTaskContract) {
            $errors[] = "Generated current-stage task contract [{$taskContractRelative}] does not match stage [{$stageId}].";
        }

        $taskContractPath = $root.'/'.$taskContractRelative;
        if (! is_file($taskContractPath)) {
            $errors[] = 'Missing current-stage task contract: '.basename($taskContractPath);
        } else {
            $taskContract = (string) file_get_contents($taskContractPath);

            foreach ($requiredTemplateSections as $section) {
                if (! str_contains($taskContract, $section)) {
                    $errors[] = basename($taskContractPath)." is missing official-source evidence section: {$section}";
                }
            }
        }
    }
}

$composerPath = $root.'/composer.json';
try {
    /** @var array<string, mixed> $composer */
    $composer = json_decode((string) file_get_contents($composerPath), true, flags: JSON_THROW_ON_ERROR);
} catch (JsonException $exception) {
    $errors[] = 'composer.json is invalid JSON: '.$exception->getMessage();
    $composer = [];
}

$scripts = is_array($composer['scripts'] ?? null) ? $composer['scripts'] : [];
if (($scripts['official-sources:verify'] ?? null) !== '@php scripts/verify-official-sources.php') {
    $errors[] = 'composer.json must define official-sources:verify.';
}

$qualityVerify = $scripts['quality:verify'] ?? [];
if (! is_array($qualityVerify) || ! in_array('@official-sources:verify', $qualityVerify, true)) {
    $errors[] = 'quality:verify must execute @official-sources:verify.';
}

if ($errors !== []) {
    fwrite(STDERR, "Official-source governance verification failed:\n- ".implode("\n- ", array_unique($errors))."\n");
    exit(1);
}

fwrite(STDOUT, "Official-source governance verification passed.\n");
