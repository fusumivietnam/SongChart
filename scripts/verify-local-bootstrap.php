<?php

declare(strict_types=1);

$required = [
    'app/Console/Commands/SetupLocalCommand.php' => ['songchart:setup-local', "environment('local', 'testing')", 'admin:ensure-local'],
    'app/Support/Local/LocalReadinessReport.php' => ['database', 'migrations', 'vite_build'],
    'app/Http/Controllers/Development/StatusController.php' => ['development.status'],
    'resources/views/development/status.blade.php' => ['data-development-status', 'Important URLs'],
    'tests/Feature/Development/LocalBootstrapTest.php' => ['RefreshDatabase', '--demo'],
    'docs/foundation/STAGE_16_1_LOCAL_BOOTSTRAP.md' => ['No default privileged account', '/development/status'],
];

foreach ($required as $file => $needles) {
    if (! is_file($file)) {
        fwrite(STDERR, "Missing {$file}\n");
        exit(1);
    }

    $content = file_get_contents($file);
    foreach ($needles as $needle) {
        if (! str_contains($content, $needle)) {
            fwrite(STDERR, "Missing {$needle} in {$file}\n");
            exit(1);
        }
    }
}

$routes = file_get_contents('routes/web.php');
if (! str_contains($routes, "app()->environment(['local', 'testing'])") || ! str_contains($routes, "'/development/status'")) {
    fwrite(STDERR, "Development status route is not local/testing guarded.\n");
    exit(1);
}

echo "Local bootstrap governance verified.\n";
