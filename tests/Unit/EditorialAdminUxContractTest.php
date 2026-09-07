<?php

declare(strict_types=1);

it('keeps Stage 21 editorial admission flow bounded and operator-first', function (): void {
    $root = dirname(__DIR__, 2);
    $index = (string) file_get_contents($root.'/resources/views/admin/canonical-admissions/index.blade.php');
    $show = (string) file_get_contents($root.'/resources/views/admin/canonical-admissions/show.blade.php');
    $sidebar = (string) file_get_contents($root.'/resources/views/components/admin/sidebar.blade.php');
    $topbar = (string) file_get_contents($root.'/resources/views/components/admin/topbar.blade.php');
    $layout = (string) file_get_contents($root.'/resources/views/layouts/admin.blade.php');
    $contract = (string) file_get_contents($root.'/docs/foundation/STAGE_21_0_TASK_CONTRACT.md');

    expect($contract)
        ->toContain('21.0A — Admission queue information hierarchy')
        ->toContain('21.0B — Decision safety and review context')
        ->toContain('21.0C — Editorial navigation and accessibility')
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
        ->toContain('Nếu chấp nhận')
        ->toContain('Nếu từ chối')
        ->toContain('Quyết định chỉ áp dụng cho đề xuất đang xem')
        ->toContain('Dữ liệu SongChart không thay đổi')
        ->toContain('Đề xuất và lý do từ chối vẫn được giữ lại trong lịch sử duyệt')
        ->toContain('Chấp nhận và cập nhật dữ liệu')
        ->toContain('Từ chối đề xuất')
        ->toContain('route(\'admin.canonical-admissions.decide\', $admission)')
        ->toContain('name="action" value="apply"')
        ->toContain('name="action" value="reject"')
        ->toContain('required minlength="10" maxlength="2000"')
        ->toContain('aria-describedby="decision-rationale-help decision-rationale-error"')
        ->toContain('aria-describedby="apply-consequence"')
        ->toContain('aria-describedby="reject-consequence"')
        ->toContain('@error(\'rationale\')')
        ->toContain('role="alert"')
        ->toContain('min-h-11')
        ->and($sidebar)
        ->toContain('id="admin-sidebar"')
        ->toContain('aria-label="Khu vực quản trị"')
        ->toContain('aria-current="page"')
        ->toContain('Duyệt thay đổi dữ liệu')
        ->toContain('Nhật ký quản trị')
        ->and($topbar)
        ->toContain('aria-controls="admin-sidebar"')
        ->toContain(':aria-expanded="sidebarOpen.toString()"')
        ->toContain('Quản trị nội dung và dữ liệu SongChart')
        ->not->toContain('admin-search')
        ->not->toContain('aria-label="Thông báo"')
        ->and($layout)
        ->toContain('href="#admin-main"')
        ->toContain('Bỏ qua điều hướng, đến nội dung chính')
        ->toContain('id="admin-main"')
        ->toContain('tabindex="-1"')
        ->toContain('aria-hidden="true"');
});
