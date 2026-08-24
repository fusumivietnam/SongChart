<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$errors = [];
$required = [
    'app/Support/Admin/ProviderOperationsConsole.php',
    'app/Http/Controllers/Admin/OperationsController.php',
    'resources/views/admin/operations/providers.blade.php',
    'resources/views/admin/operations/provider-show.blade.php',
    'resources/views/admin/operations/imports.blade.php',
    'resources/views/admin/operations/import-show.blade.php',
    'resources/views/admin/operations/quarantine.blade.php',
    'tests/Feature/ProviderOperationsConsoleTest.php',
    'docs/foundation/STAGE_16_6_TASK_CONTRACT.md',
    'docs/foundation/STAGE_16_6_VALIDATION_REPORT.md',
];
foreach ($required as $file) {
    if (! is_file($root.'/'.$file)) {
        $errors[] = "Missing Stage 16.6 provider-operations file: {$file}.";
    }
}

$routes = is_file($root.'/routes/web.php') ? (string) file_get_contents($root.'/routes/web.php') : '';
foreach (["name('providers.show')", "name('imports.show')"] as $needle) {
    if (! str_contains($routes, $needle)) {
        $errors[] = "routes/web.php missing provider-operations route marker {$needle}.";
    }
}

$controllerPath = $root.'/app/Http/Controllers/Admin/OperationsController.php';
$controller = is_file($controllerPath) ? (string) file_get_contents($controllerPath) : '';
foreach (['admin.providers.show', 'admin.imports.show'] as $key) {
    if (! str_contains($controller, $key)) {
        $errors[] = "OperationsController missing executable use-case key {$key}.";
    }
}

$consolePath = $root.'/app/Support/Admin/ProviderOperationsConsole.php';
$console = is_file($consolePath) ? (string) file_get_contents($consolePath) : '';
foreach (['->update(', '->delete(', '->create('] as $mutation) {
    if (str_contains($console, $mutation)) {
        $errors[] = "ProviderOperationsConsole must remain read-only; found {$mutation}.";
    }
}

if ($errors !== []) {
    fwrite(STDERR, "Provider operations console verification failed:\n- ".implode("\n- ", $errors)."\n");
    exit(1);
}

fwrite(STDOUT, "Provider operations console verification passed.\n");
