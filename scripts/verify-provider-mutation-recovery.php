<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$checks = [
    'app/Support/Providers/Operations/ProviderMutationService.php' => ['lockForUpdate()', "'idempotency_key'", "'retire'", "'retry'", "'resume'", "'cancel'"],
    'routes/web.php' => ['can:manage-providers', 'password.confirm', 'providers.mutate', 'imports.recover'],
    'database/migrations/2026_08_10_000100_create_provider_operation_audits_table.php' => ['provider_operation_audits', 'idempotency_key', 'before_state', 'after_state'],
];
$errors = [];
foreach ($checks as $file => $needles) {
    $body = file_get_contents($root.'/'.$file);
    if ($body === false) {
        $errors[] = "Missing $file";

        continue;
    } foreach ($needles as $needle) {
        if (! str_contains($body, $needle)) {
            $errors[] = "$file missing $needle";
        }
    }
}
if ($errors !== []) {
    fwrite(STDERR, "Provider mutation/recovery verification failed:\n- ".implode("\n- ", $errors)."\n");
    exit(1);
} echo "Provider mutation and recovery verification passed.\n";
