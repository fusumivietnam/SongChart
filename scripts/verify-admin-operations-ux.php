<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$errors = [];
$checks = [
    'app/Support/Admin/AdminOperationsPresentation.php' => ['providerStatus(', 'importStatus(', 'providerAction(', 'recoveryAction('],
    'resources/views/components/admin/sidebar.blade.php' => ['Nguồn dữ liệu', 'Nhập dữ liệu', 'Lịch sử tác vụ', 'Dữ liệu cần rà soát', 'Xung đột định danh', 'Vận hành theo công việc'],
    'resources/views/admin/dashboard.blade.php' => ['attention-first', 'Cần xử lý', 'Nhập dữ liệu', 'data-admin-technical-details'],
    'resources/views/admin/operations/providers.blade.php' => ['Thiết lập nguồn dữ liệu', 'YouTube Data API key', 'Secret được mã hóa', 'providers.configuration.update'],
    'resources/views/admin/operations/import-preview.blade.php' => ['Bạn chỉ cần nhập điều bạn đang biết', 'Nghệ sĩ / nhóm nhạc', 'Tên bài hát', 'Đoạn lời bài hát', 'Chế độ nâng cao: ID + JSON', 'admin.imports.search', 'admin.imports.select'],
    'resources/views/admin/operations/imports.blade.php' => ['Bắt đầu nhập dữ liệu', 'admin.imports.preview'],
    'resources/views/admin/operations/provider-show.blade.php' => ['Tạm ngừng nguồn', 'Ngừng sử dụng vĩnh viễn', 'data-admin-technical-details'],
    'resources/views/admin/operations/import-show.blade.php' => ['Khôi phục tác vụ', 'Tiếp tục từ điểm đã lưu', 'data-admin-technical-details'],
    'tests/Feature/AdminOperationsUxTest.php' => ['attention center', 'operational language', 'role aware'],
];

foreach ($checks as $file => $needles) {
    $path = $root.'/'.$file;
    $body = is_file($path) ? (string) file_get_contents($path) : '';

    if ($body === '') {
        $errors[] = "Missing {$file}.";

        continue;
    }

    foreach ($needles as $needle) {
        if (! str_contains($body, $needle)) {
            $errors[] = "{$file} missing {$needle}.";
        }
    }
}

$providerView = (string) file_get_contents($root.'/resources/views/admin/operations/provider-show.blade.php');
$importView = (string) file_get_contents($root.'/resources/views/admin/operations/import-show.blade.php');
$providersView = (string) file_get_contents($root.'/resources/views/admin/operations/providers.blade.php');

foreach ([$providerView, $importView, $providersView] as $body) {
    if (! str_contains($body, 'type="hidden" name="idempotency_key"')) {
        $errors[] = 'Privileged provider/import forms must retain generated hidden idempotency keys.';
    }
}

if (! str_contains($providersView, 'type="password" name="youtube_api_key"')) {
    $errors[] = 'Provider API secrets must use a password input and must never be rendered as a stored value.';
}
if (str_contains($providersView, "value=\"{{ old('youtube_api_key'")) {
    $errors[] = 'Stored provider secrets must never be round-tripped into Admin HTML.';
}

if ($errors !== []) {
    fwrite(STDERR, "Admin operations UX verification failed:\n- ".implode("\n- ", $errors)."\n");
    exit(1);
}

fwrite(STDOUT, "Admin operations UX verification passed.\n");
