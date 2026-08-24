<?php

declare(strict_types=1);

use App\Support\Engineering\RepositoryContractResolver;

$root = dirname(__DIR__);
require_once $root.'/app/Support/Engineering/RepositoryContractResolver.php';
$errors = [];
$read = static fn (string $path): string => (string) file_get_contents($root.'/'.$path);

$composer = json_decode($read('composer.json'), true);
$scripts = is_array($composer['scripts'] ?? null) ? $composer['scripts'] : [];
$resolver = new RepositoryContractResolver($root);
$databaseContract = $resolver->value('database-test');
$expectedDatabaseScripts = is_array($databaseContract['scripts'] ?? null) ? $databaseContract['scripts'] : [];
$adminFiles = [
    'app/Support/Admin/AdminDashboardSnapshot.php',
    'app/Support/Admin/AdminInformationArchitecture.php',
    'app/Support/Admin/CatalogAdministration.php',
    'app/Support/Admin/ProviderOperationsConsole.php',
];
foreach ($adminFiles as $file) {
    $source = $read($file);
    if (str_contains($source, 'Schema::hasTable(') || str_contains($source, 'Facades\\Schema')) {
        $errors[] = "{$file} must not probe schema existence on request paths.";
    }
}

$ia = $read('app/Support/Admin/AdminInformationArchitecture.php');
foreach (['function providers(', 'function imports(', 'function quarantine('] as $needle) {
    if (str_contains($ia, $needle)) {
        $errors[] = "AdminInformationArchitecture retains provider operational ownership: {$needle}";
    }
}

$catalog = $read('app/Support/Admin/CatalogAdministration.php');
if (! str_contains($catalog, "->limit(100)\n            ->get();")) {
    $errors[] = 'CatalogAdministration must bound metadata conflict history.';
}

foreach (['test', 'test:feature', 'test:postgres'] as $name) {
    $expected = $expectedDatabaseScripts[$name] ?? null;
    if (! is_string($expected) || ($scripts[$name] ?? null) !== $expected) {
        $errors[] = "{$name} must use the shared database-test contract authority.";
    }
}

foreach (['test:all', 'test:postgres-clean'] as $removedAlias) {
    if (array_key_exists($removedAlias, $scripts)) {
        $errors[] = "Removed database-test alias [{$removedAlias}] must not be restored.";
    }
}

$config = $read('config/songchart.php');
$middleware = $read('app/Http/Middleware/EnsureConfirmedTwoFactorAuthentication.php');
if (! str_contains($config, "in_array(\$adminTwoFactorMode, ['required', 'disabled'], true)")) {
    $errors[] = 'SongChart security config must validate admin 2FA mode against required|disabled.';
}
if (! str_contains($middleware, "=== 'required'")) {
    $errors[] = '2FA middleware must consume the validated required|disabled contract.';
}

if ($errors !== []) {
    fwrite(STDERR, "Runtime authority closure verification failed:\n- ".implode("\n- ", array_unique($errors))."\n");
    exit(1);
}

fwrite(STDOUT, 'Runtime authority closure verification passed.'.PHP_EOL);
