<?php

declare(strict_types=1);

$required = [
    'docs/project/docs/ENGINEERING_WORKFLOW.md' => ['Mandatory state machine', 'Change-surface discipline', 'Impact analysis', 'Review gates', 'Completion claims'],
    'docs/project/docs/AI_PROGRESS_WORKFLOW.md' => ['Sticky PR dashboard', 'Notification policy', 'Daily owner workflow', 'Failure behavior', 'Security', '<!-- songchart-ai-progress -->', 'connector'],
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
    'Progress summary — prepared',
    'Sticky PR dashboard and owner notifications are connector/task-owned',
    'Sticky PR dashboard / owner notification: updated by authorized ChatGPT/GitHub connector automation',
] as $needle) {
    if (! str_contains($autoClosure, $needle)) {
        fwrite(STDERR, "Auto Closure progress handoff missing {$needle}\n");
        exit(1);
    }
}

foreach (['issues: write', 'pull-requests: write', 'Update sticky PR progress', '<!-- songchart-ai-progress -->'] as $forbidden) {
    if (str_contains($autoClosure, $forbidden)) {
        fwrite(STDERR, "GitHub Actions must remain read-only toward PR progress UI: {$forbidden}\n");
        exit(1);
    }
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
