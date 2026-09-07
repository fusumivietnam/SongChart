<?php

declare(strict_types=1);

it('keeps Stage 21 editorial admission queue bounded by existing governed actions', function (): void {
    $root = dirname(__DIR__, 2);
    $view = (string) file_get_contents($root.'/resources/views/admin/canonical-admissions/index.blade.php');
    $contract = (string) file_get_contents($root.'/docs/foundation/STAGE_21_0_TASK_CONTRACT.md');

    expect($contract)
        ->toContain('21.0A — Admission queue information hierarchy')
        ->toContain('Canonical mutation remains owned by the existing governed admission service.')
        ->toContain('No Stage 21 tranche may silently add schema/domain concepts to solve a presentation problem.')
        ->and($view)
        ->toContain('Quy trình duyệt')
        ->toContain('Hàng chờ quyết định')
        ->toContain('Evidence chưa xếp hàng')
        ->toContain('route(\'admin.canonical-admissions.show\', $decision)')
        ->toContain('route(\'admin.canonical-admissions.stage\', $assertion)')
        ->toContain('aria-current="page"')
        ->toContain('aria-labelledby="admission-decisions-heading"')
        ->not->toContain('route(\'admin.canonical-admissions.decide\'');
});
