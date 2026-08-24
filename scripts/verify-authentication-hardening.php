<?php

declare(strict_types=1);

$required = [
    'app/Console/Commands/CreateAdminCommand.php',
    'app/Http/Middleware/EnsureUserIsActive.php',
    'app/Http/Middleware/EnsureConfirmedTwoFactorAuthentication.php',
    'tests/Feature/Authentication/PrivilegedOperationsHardeningTest.php',
    'tests/Architecture/AuthenticationHardeningBoundaryTest.php',
    'docs/security/AUTHENTICATION_AND_PRIVILEGED_OPERATIONS.md',
    'docs/project/security/authorization-contract.json',
    'app/Enums/Capability.php',
    'app/Providers/AuthorizationServiceProvider.php',
    'app/Support/Auth/AuthorizationMatrix.php',
    'tests/Unit/AuthorizationMatrixTest.php',
    'tests/Feature/AuthorizationGateTest.php',
];

$errors = [];
foreach ($required as $path) {
    if (! is_file(__DIR__.'/../'.$path)) {
        $errors[] = "Missing required file: {$path}";
    }
}

$routes = file_get_contents(__DIR__.'/../routes/web.php');
$seeder = file_get_contents(__DIR__.'/../database/seeders/DatabaseSeeder.php');
$user = file_get_contents(__DIR__.'/../app/Models/User.php');

foreach (["'active'", "'two-factor.confirmed'", "'password.confirm'"] as $needle) {
    if (! str_contains($routes, $needle)) {
        $errors[] = "Missing route safeguard: {$needle}";
    }
}

if (str_contains($seeder, 'ChangeMe123!') || str_contains($seeder, 'admin@songchart.test')) {
    $errors[] = 'Default administrator credentials remain in the database seeder.';
}

if (str_contains($user, "'role', 'is_active'")) {
    $errors[] = 'Privileged user attributes remain mass assignable.';
}

$authorizationContract = json_decode(
    (string) file_get_contents(__DIR__.'/../docs/project/security/authorization-contract.json'),
    true,
    512,
    JSON_THROW_ON_ERROR,
);
$capabilitySource = (string) file_get_contents(__DIR__.'/../app/Enums/Capability.php');
$roleSource = (string) file_get_contents(__DIR__.'/../app/Enums/UserRole.php');
$providerSource = (string) file_get_contents(__DIR__.'/../app/Providers/AuthorizationServiceProvider.php');

if (($authorizationContract['rules']['role_matrix_is_single_source_of_truth'] ?? false) !== true) {
    $errors[] = 'Authorization contract must declare the role matrix as the single source of truth.';
}

foreach (($authorizationContract['roles'] ?? []) as $role => $capabilities) {
    if (! is_string($role) || ! is_array($capabilities)) {
        $errors[] = 'Authorization role matrix is malformed.';

        continue;
    }

    foreach ($capabilities as $capability) {
        if (! is_string($capability) || ! str_contains($capabilitySource, "'{$capability}'")) {
            $errors[] = "Authorization role [{$role}] references an unknown Capability enum value [{$capability}].";
        }
    }
}

foreach (['canAccessAdmin', 'canManageExtensions', 'canManageProviders', 'canReviewIdentityConflicts', 'canViewOperations'] as $legacyMethod) {
    if (str_contains($roleSource, $legacyMethod) || str_contains($user, $legacyMethod)) {
        $errors[] = "Legacy role authorization method [{$legacyMethod}] must not be restored.";
    }
}

if (! str_contains($providerSource, 'foreach (Capability::cases() as $capability)')
    || ! str_contains($providerSource, 'AuthorizationMatrix::class')
) {
    $errors[] = 'Laravel Gates must be registered from Capability::cases() through AuthorizationMatrix.';
}

foreach (($authorizationContract['forbidden_legacy_surfaces'] ?? []) as $relative) {
    if (is_string($relative) && is_file(__DIR__.'/../'.$relative)) {
        $errors[] = "Forbidden legacy authorization surface remains [{$relative}].";
    }
}

$legacyMethods = array_values(array_filter(
    (array) ($authorizationContract['forbidden_legacy_methods'] ?? []),
    'is_string',
));
$legacyScanRoots = [
    __DIR__.'/../app',
    __DIR__.'/../routes',
];
foreach ($legacyScanRoots as $scanRoot) {
    if (! is_dir($scanRoot)) {
        continue;
    }

    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($scanRoot, FilesystemIterator::SKIP_DOTS),
    );
    foreach ($iterator as $file) {
        if (! $file->isFile() || strtolower($file->getExtension()) !== 'php') {
            continue;
        }

        $relative = str_replace('\\', '/', substr($file->getPathname(), strlen(__DIR__.'/../')));
        if (in_array($relative, [
            'app/Support/Admin/PrivilegedUserAdministration.php',
        ], true)) {
            continue;
        }

        $source = (string) file_get_contents($file->getPathname());
        foreach ($legacyMethods as $legacyMethod) {
            if (str_contains($source, $legacyMethod.'(')) {
                $errors[] = "Forbidden legacy authorization method [{$legacyMethod}] remains in [{$relative}].";
            }
        }
    }
}

$capabilityValues = [];
preg_match_all("/case\\s+[A-Za-z0-9_]+\\s*=\\s*'([^']+)'/", $capabilitySource, $capabilityMatches);
foreach ($capabilityMatches[1] ?? [] as $value) {
    if (is_string($value)) {
        $capabilityValues[$value] = true;
    }
}

$gateSurfaces = [
    'routes/web.php',
];
$viewsRoot = __DIR__.'/../resources/views';
if (is_dir($viewsRoot)) {
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($viewsRoot, FilesystemIterator::SKIP_DOTS),
    );
    foreach ($iterator as $file) {
        if ($file->isFile() && str_ends_with($file->getFilename(), '.blade.php')) {
            $gateSurfaces[] = str_replace('\\', '/', substr($file->getPathname(), strlen(__DIR__.'/../')));
        }
    }
}

foreach (array_values(array_unique($gateSurfaces)) as $relative) {
    $surface = (string) @file_get_contents(__DIR__.'/../'.$relative);
    preg_match_all("/(?:can:|@can\\(['\"]|->can\\(['\"]|\\?->can\\(['\"])([A-Za-z0-9_-]+)/", $surface, $matches);
    foreach (array_values(array_unique($matches[1] ?? [])) as $gateName) {
        if (! isset($capabilityValues[$gateName])) {
            $errors[] = "Authorization surface [{$relative}] references unregistered Gate [{$gateName}].";
        }
    }
}

$authorizationSurfaces = [
    'routes/web.php',
    'resources/views/components/shell/frontend-header.blade.php',
    'resources/views/components/admin/sidebar.blade.php',
];

foreach ($authorizationSurfaces as $relative) {
    $surface = (string) file_get_contents(__DIR__.'/../'.$relative);
    preg_match_all("/(?:can:|@can\(['\"]|->can\(['\"]|\?->can\(['\"])([A-Za-z0-9_-]+|viewHorizon|viewPulse)/", $surface, $matches);

    foreach (array_values(array_unique($matches[1] ?? [])) as $gateName) {
        if (! str_contains($capabilitySource, "'{$gateName}'")) {
            $errors[] = "Authorization surface [{$relative}] references unregistered Gate [{$gateName}].";
        }
    }
}

if ($errors !== []) {
    fwrite(STDERR, "Authentication hardening verification failed:\n- ".implode("\n- ", $errors)."\n");
    exit(1);
}

echo "Authentication and privileged operations verification passed.\n";
