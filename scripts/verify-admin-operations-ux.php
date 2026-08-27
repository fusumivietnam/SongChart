<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$errors = [];
$checks = [
    'app/Support/Admin/AdminOperationsPresentation.php' => ['providerStatus(', 'importStatus(', 'providerAction(', 'recoveryAction('],
    'resources/views/components/admin/sidebar.blade.php' => ['Nguồn dữ liệu', 'Nhập dữ liệu', 'Lịch sử tác vụ', 'Dữ liệu cần rà soát', 'Xung đột định danh', 'Vận hành theo công việc'],
    'resources/views/admin/dashboard.blade.php' => ['attention-first', 'Cần xử lý', 'Nhập dữ liệu', 'data-admin-technical-details'],
    'resources/views/admin/operations/providers.blade.php' => ['Nguồn dữ liệu'],
    'resources/views/admin/operations/system.blade.php' => ['Thiết lập hệ thống', 'API & tích hợp', 'YouTube Data API credential pool', 'Secret được mã hóa', 'admin.providers.configuration.update'],
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
$systemView = (string) file_get_contents($root.'/resources/views/admin/operations/system.blade.php');

foreach ([$providerView, $importView, $systemView] as $body) {
    if (! str_contains($body, 'type="hidden" name="idempotency_key"')) {
        $errors[] = 'Privileged provider/import/configuration forms must retain generated hidden idempotency keys.';
    }
}

if (! str_contains($systemView, 'name="youtube_api_keys"')) {
    $errors[] = 'System settings must expose the YouTube credential pool input only when the adapter is operational.';
}
if (str_contains($systemView, "old('youtube_api_keys')")
    || str_contains($systemView, "value=\"{{ old('youtube_api_key'")
    || str_contains($systemView, "value=\"{{ old('youtube_api_keys'")) {
    $errors[] = 'Provider API secrets must never be round-tripped into Admin HTML.';
}

$providersView = (string) file_get_contents($root.'/resources/views/admin/operations/providers.blade.php');
foreach (['youtube_api_key', 'youtube_api_keys', 'providers.configuration.update'] as $credentialSignal) {
    if (str_contains($providersView, $credentialSignal)) {
        $errors[] = "Provider operations list must not own credential configuration [{$credentialSignal}].";
    }
}

if ($errors !== []) {
    fwrite(STDERR, "Admin operations UX verification failed:\n- ".implode("\n- ", $errors)."\n");
    exit(1);
}

fwrite(STDOUT, "Admin operations UX verification passed.\n");
