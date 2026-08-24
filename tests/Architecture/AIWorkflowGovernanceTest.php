<?php

declare(strict_types=1);

it('keeps AI workflow and change-surface governance executable', function (): void {
    $composer = json_decode((string) file_get_contents(base_path('composer.json')), true, 512, JSON_THROW_ON_ERROR);

    expect(base_path('docs/project/docs/ENGINEERING_WORKFLOW.md'))->toBeFile()
        ->and(base_path('docs/templates/IMPLEMENTATION_PLAN_TEMPLATE.md'))->toBeFile()
        ->and(base_path('docs/templates/DEBUGGING_REPORT_TEMPLATE.md'))->toBeFile()
        ->and(base_path('docs/templates/VALIDATION_REPORT_TEMPLATE.md'))->toBeFile()
        ->and(base_path('docs/project/stack/impact-test-map.json'))->toBeFile()
        ->and($composer['scripts']['workflow:verify'] ?? null)->toBe('@php scripts/verify-ai-workflow.php')
        ->and($composer['scripts']['change-surface:verify'] ?? null)->toBe('@php scripts/verify-change-surface.php')
        ->and($composer['scripts']['no-placeholders:verify'] ?? null)->toBe('@php scripts/verify-no-placeholders.php')
        ->and($composer['scripts']['quality:verify'])->toContain('@workflow:verify', '@no-placeholders:verify');
});
