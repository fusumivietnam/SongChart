<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$errors = [];

$required = [
    'app/Providers/FortifyServiceProvider.php',
    'app/Providers/AuthorizationServiceProvider.php',
    'app/Http/Controllers/Admin/ExtensionController.php',
    'app/Http/Requests/Search/SearchRequest.php',
    'app/Actions/Search/BuildSearchPage.php',
    'docs/foundation/STAGE_11_3_LARAVEL_FEATURE_ALIGNMENT_AUDIT.md',
];

foreach ($required as $relative) {
    if (! is_file($root.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $relative))) {
        $errors[] = "Missing alignment baseline file: {$relative}";
    }
}

/** @return array<string, string> */
function phpFiles(string $directory): array
{
    if (! is_dir($directory)) {
        return [];
    }

    $files = [];
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($directory, FilesystemIterator::SKIP_DOTS)
    );

    /** @var SplFileInfo $file */
    foreach ($iterator as $file) {
        if ($file->isFile() && strtolower($file->getExtension()) === 'php') {
            $contents = file_get_contents($file->getPathname());
            if ($contents !== false) {
                $files[$file->getPathname()] = $contents;
            }
        }
    }

    return $files;
}

$appFiles = phpFiles($root.DIRECTORY_SEPARATOR.'app');

foreach ($appFiles as $path => $contents) {
    $relative = str_replace($root.DIRECTORY_SEPARATOR, '', $path);

    if (preg_match('/\bcurl_(?:init|exec|setopt|multi_)/i', $contents) === 1) {
        $errors[] = "Raw cURL is prohibited in application code: {$relative}";
    }

    if (preg_match('/\benv\s*\(/', $contents) === 1) {
        $errors[] = "env() is only allowed in config files: {$relative}";
    }

    if (str_contains($relative, 'app'.DIRECTORY_SEPARATOR.'Http'.DIRECTORY_SEPARATOR.'Controllers'.DIRECTORY_SEPARATOR)
        && preg_match('/\bMail::/', $contents) === 1) {
        $errors[] = "Direct Mail facade usage is prohibited in controllers: {$relative}";
    }

    if (preg_match('/while\s*\(\s*true\s*\).*queue/is', $contents) === 1) {
        $errors[] = "Custom queue worker loop is prohibited: {$relative}";
    }
}

$bootstrap = @file_get_contents($root.DIRECTORY_SEPARATOR.'bootstrap'.DIRECTORY_SEPARATOR.'app.php') ?: '';
$routes = @file_get_contents($root.DIRECTORY_SEPARATOR.'routes'.DIRECTORY_SEPARATOR.'web.php') ?: '';
$extensionController = @file_get_contents($root.DIRECTORY_SEPARATOR.'app'.DIRECTORY_SEPARATOR.'Http'.DIRECTORY_SEPARATOR.'Controllers'.DIRECTORY_SEPARATOR.'Admin'.DIRECTORY_SEPARATOR.'ExtensionController.php') ?: '';

$searchController = @file_get_contents($root.DIRECTORY_SEPARATOR.'app'.DIRECTORY_SEPARATOR.'Http'.DIRECTORY_SEPARATOR.'Controllers'.DIRECTORY_SEPARATOR.'Search'.DIRECTORY_SEPARATOR.'SearchController.php') ?: '';

if (str_contains($searchController, '->validate(') || str_contains($searchController, 'Illuminate\Http\Request')) {
    $errors[] = 'Public search validation must remain in SearchRequest.';
}

if (! str_contains($searchController, 'BuildSearchPage') || ! str_contains($searchController, 'SearchRequest')) {
    $errors[] = 'Public search must use SearchRequest and BuildSearchPage action boundaries.';
}

if (str_contains($extensionController, 'App\Extensions\ExtensionManager')
    || str_contains($extensionController, 'App\Extensions\ExtensionInstaller')
    || str_contains($extensionController, 'App\Extensions\ExtensionUpgradeManager')
    || str_contains($extensionController, 'App\Extensions\ExtensionPreflight')) {
    $errors[] = 'ExtensionController must delegate lifecycle orchestration to application Actions.';
}

$authorizationProvider = @file_get_contents($root.DIRECTORY_SEPARATOR.'app'.DIRECTORY_SEPARATOR.'Providers'.DIRECTORY_SEPARATOR.'AuthorizationServiceProvider.php') ?: '';
$fortifyProvider = @file_get_contents($root.DIRECTORY_SEPARATOR.'app'.DIRECTORY_SEPARATOR.'Providers'.DIRECTORY_SEPARATOR.'FortifyServiceProvider.php') ?: '';

if (str_contains($bootstrap, 'EnsureUserIsAdmin') || str_contains($routes, "'auth', 'verified', 'admin'")) {
    $errors[] = 'Custom admin middleware must not replace the access-admin Gate.';
}

$authorizationContract = json_decode(
    (string) @file_get_contents($root.DIRECTORY_SEPARATOR.'docs'.DIRECTORY_SEPARATOR.'project'.DIRECTORY_SEPARATOR.'security'.DIRECTORY_SEPARATOR.'authorization-contract.json'),
    true,
    512,
    JSON_THROW_ON_ERROR,
);
$capabilityEnum = @file_get_contents($root.DIRECTORY_SEPARATOR.'app'.DIRECTORY_SEPARATOR.'Enums'.DIRECTORY_SEPARATOR.'Capability.php') ?: '';

if (! str_contains($routes, 'can:access-admin')
    || ! str_contains($capabilityEnum, "case AccessAdmin = 'access-admin';")
    || ! str_contains($authorizationProvider, 'foreach (Capability::cases() as $capability)')
    || ! str_contains($authorizationProvider, 'AuthorizationMatrix::class')
    || ! in_array('access-admin', $authorizationContract['roles']['super_admin'] ?? [], true)
) {
    $errors[] = 'Admin authorization must resolve the named access-admin capability through the shared Laravel Gate authorization authority.';
}

if (str_contains($extensionController, '->validate(') || str_contains($extensionController, 'Illuminate\\Http\\Request')) {
    $errors[] = 'Extension write validation must remain in Form Requests.';
}

if (! str_contains($fortifyProvider, "RateLimiter::for('login'") || ! str_contains($fortifyProvider, "RateLimiter::for('two-factor'")) {
    $errors[] = 'Fortify login and two-factor named rate limiters are required.';
}

$providerJob = @file_get_contents($root.DIRECTORY_SEPARATOR.'app'.DIRECTORY_SEPARATOR.'Jobs'.DIRECTORY_SEPARATOR.'Providers'.DIRECTORY_SEPARATOR.'CheckProviderHealth.php') ?: '';
$consoleRoutes = @file_get_contents($root.DIRECTORY_SEPARATOR.'routes'.DIRECTORY_SEPARATOR.'console.php') ?: '';

if (! str_contains($providerJob, 'ShouldQueue') || ! str_contains($providerJob, 'ShouldBeUnique')) {
    $errors[] = 'Provider health work must remain a unique Laravel queued job.';
}

if (! str_contains($consoleRoutes, "Schedule::command('providers:health-check')")
    || ! str_contains($consoleRoutes, 'withoutOverlapping')
    || ! str_contains($consoleRoutes, 'onOneServer')) {
    $errors[] = 'Provider health dispatch must remain on the protected Laravel Scheduler boundary.';
}

if ($errors !== []) {
    fwrite(STDERR, "Laravel feature alignment verification failed:\n- ".implode("\n- ", array_unique($errors))."\n");
    exit(1);
}

fwrite(STDOUT, "Laravel feature alignment verification passed.\n");
