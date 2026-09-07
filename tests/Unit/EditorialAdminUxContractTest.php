<?php

declare(strict_types=1);

it('keeps Stage 21 editorial admission flow bounded and operator-first', function (): void {
    $root = dirname(__DIR__, 2);
    $index = (string) file_get_contents($root.'/resources/views/admin/canonical-admissions/index.blade.php');
    $show = (string) file_get_contents($root.'/resources/views/admin/canonical-admissions/show.blade.php');
    $contract = (string) file_get_contents($root.'/docs/foundation/STAGE_21_0_TASK_CONTRACT.md');

    expect($contract)
        ->toContain('21.0A — Admission queue information hierarchy')
        ->toContain('Canonical mutation remains owned by the existing governed admission service.')
        ->toContain('No Stage 21 tranche may silently add schema/domain concepts to solve a presentation problem.')
        ->and($index)
        ->toContain('Duyệt thay đổi dữ liệu')
        ->toContain('Cách duyệt một đề xuất')
        ->toContain('Đề xuất cần bạn xem xét')
        ->toContain('Đề xuất mới từ các nguồn dữ liệu')
        ->toContain('route(\'admin.canonical-admissions.show\', $decision)')
        ->toContain('route(\'admin.canonical-admissions.stage\', $assertion)')
        ->toContain('aria-current="page"')
        ->toContain('aria-labelledby="admission-decisions-heading"')
        ->toContain('md:hidden')
        ->toContain('hidden overflow-x-auto md:block')
        ->toContain('min-h-11')
        ->not->toContain('./songchart dev ready')
        ->not->toContain('route(\'admin.canonical-admissions.decide\'')
        ->and($show)
        ->toContain('Xem xét thay đổi dữ liệu')
        ->toContain('Chi tiết kỹ thuật')
        ->toContain('Lý do quyết định')
        ->toContain('Chấp nhận và cập nhật dữ liệu')
        ->toContain('Từ chối đề xuất')
        ->toContain('aria-describedby="decision-rationale-help"')
        ->toContain('min-h-11');
});
