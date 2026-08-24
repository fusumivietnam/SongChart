<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$errors = [];
$checks = [
    'app/Support/Admin/AdminOperationsPresentation.php' => ['providerStatus(', 'importStatus(', 'providerAction(', 'recoveryAction('],
    'resources/views/components/admin/sidebar.blade.php' => ['Nguồn dữ liệu', 'Tác vụ dữ liệu', 'Dữ liệu cần rà soát', 'Xung đột định danh', 'Vận hành theo công việc'],
    'resources/views/admin/dashboard.blade.php' => ['attention-first', 'Cần xử lý', 'data-admin-technical-details'],
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

foreach ([$providerView, $importView] as $body) {
    if (! str_contains($body, 'type="hidden" name="idempotency_key"')) {
        $errors[] = 'Idempotency keys must remain generated hidden fields, not administrator inputs.';
    }
}

if ($errors !== []) {
    fwrite(STDERR, "Admin operations UX verification failed:\n- ".implode("\n- ", $errors)."\n");
    exit(1);
}

fwrite(STDOUT, "Admin operations UX verification passed.\n");
