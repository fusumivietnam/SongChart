<?php

declare(strict_types=1);

$required = [
    'docs/project/docs/ENGINEERING_WORKFLOW.md' => ['Mandatory state machine', 'Change-surface discipline', 'Impact analysis', 'Review gates', 'Completion claims'],
    'docs/project/docs/AI_PROGRESS_WORKFLOW.md' => ['Sticky PR dashboard', 'Notification policy', 'Daily owner workflow', 'Failure behavior', 'Security', '<!-- songchart-ai-progress -->'],
    'docs/templates/IMPLEMENTATION_PLAN_TEMPLATE.md' => ['Expected files', 'Allowed incidental files', 'Impact preflight', 'Scope deviations'],
    'docs/templates/DEBUGGING_REPORT_TEMPLATE.md' => ['Root-cause hypothesis', 'Minimal corrective change', 'Patch-attempt count'],
    'docs/templates/VALIDATION_REPORT_TEMPLATE.md' => ['Evidence matrix', 'Spec-compliance review', 'Code-quality review', 'Unperformed verification'],
    'docs/project/stack/impact-test-map.json' => ['required_tests'],
];
foreach ($required as $file => $needles) {
    if (! is_file($file)) {
        fwrite(STDERR, "Missing {$file}\n");
        exit(1);
    }
    $content = (string) file_get_contents($file);
    foreach ($needles as $needle) {
        if (! str_contains($content, $needle)) {
            fwrite(STDERR, "{$file} missing {$needle}\n");
            exit(1);
        }
    }
}

$autoClosure = (string) file_get_contents('.github/workflows/auto-closure.yml');
foreach ([
    '<!-- songchart-ai-progress -->',
    'Update sticky PR progress — prepared',
    'progress-verified:',
    'Update sticky PR progress — runtime verified',
    'progress-failure:',
    'Update sticky PR progress — blocker',
    'Update sticky PR progress — exact head verified',
    'issues: write',
    'continue-on-error: true',
    'needs_owner_input',
] as $needle) {
    if (! str_contains($autoClosure, $needle)) {
        fwrite(STDERR, "Auto Closure progress contract missing {$needle}\n");
        exit(1);
    }
}

if (str_contains($autoClosure, 'pull-requests: write')) {
    fwrite(STDERR, "Progress automation must not mutate PR state.\n");
    exit(1);
}

$map = json_decode((string) file_get_contents('docs/project/stack/impact-test-map.json'), true, 512, JSON_THROW_ON_ERROR);
if (! isset($map['rules']) || ! is_array($map['rules']) || count($map['rules']) < 5) {
    fwrite(STDERR, "Impact test map is incomplete.\n");
    exit(1);
}
foreach (['authentication', 'extensions', 'providers', 'identity', 'governance'] as $skill) {
    if (! is_file(".agents/skills/{$skill}/SKILL.md")) {
        fwrite(STDERR, "Missing {$skill} skill.\n");
        exit(1);
    }
}
echo "AI development workflow verification passed.\n";
