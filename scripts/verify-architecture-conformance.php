<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$errors = [];
$required = [
    'app/Support/Search/EloquentSearchCatalog.php',
    'app/Providers/SearchServiceProvider.php',
    'app/Support/Catalog/CatalogEntityResolver.php',
    'app/Domain/Providers/Enums/ProviderSyncStatus.php',
    'app/Domain/Providers/Enums/ProviderSyncOperation.php',
    'tests/Architecture/ArchitectureConformanceTest.php',
    'docs/project/domain/application-data-boundary.json',
    'docs/project/performance/query-budget-contract.json',
    'docs/project/stack/runtime-environments.json',
    'config/database.php',
    'app/Application/Admin/Queries/ExtensionReadModel.php',
];
foreach ($required as $file) {
    if (! is_file($root.'/'.$file)) {
        $errors[] = "Missing {$file}";
    }
}
$provider = file_get_contents($root.'/app/Providers/SearchServiceProvider.php') ?: '';
if (! str_contains($provider, 'EloquentSearchCatalog::class') || ! str_contains(file_get_contents($root.'/config/songchart.php') ?: '', 'SONGCHART_DEMO_SEARCH')) {
    $errors[] = 'Search binding is not production-safe.';
}
$composer = json_decode(file_get_contents($root.'/composer.json') ?: '', true);
if (($composer['scripts']['setup'][3] ?? null) !== 'npm ci') {
    $errors[] = 'composer setup must use npm ci.';
}
$routes = file_get_contents($root.'/routes/web.php') ?: '';
if (str_contains($routes, "Route::middleware('web')->group")) {
    $errors[] = 'routes/web.php contains redundant web middleware.';
}
if (str_contains(file_get_contents($root.'/resources/views/ui-preview/sections/patterns.blade.php') ?: '', 'href="#"')) {
    $errors[] = 'UI preview contains dead href.';
}

$frontendDesignContract = (string) file_get_contents($root.'/docs/ui/SONGCHART_FRONTEND_DESIGN_CONTRACT.md');
foreach ([
    'resources/css/tokens.css',
    'resources/views/layouts/frontend.blade.php',
    'resources/views/layouts/admin.blade.php',
    'resources/views/components/ui/',
    'resources/views/components/entity/',
    'resources/views/components/provider/',
    'resources/views/components/search/',
    'resources/views/components/shell/',
    'resources/views/home.blade.php',
    'resources/views/search/',
    'resources/views/entities/',
    'resources/views/catalog/',
    'resources/views/charts/',
    'resources/views/account/',
    'resources/views/auth/',
    'resources/views/admin/',
    'resources/views/ui-preview/',
] as $ownedUiPath) {
    if (str_contains($frontendDesignContract, $ownedUiPath) === false) {
        $errors[] = "Frontend design contract is missing executable UI owner [{$ownedUiPath}].";
    }
}
if (preg_match('/^resources\/views\/pages\/$/m', $frontendDesignContract) === 1) {
    $errors[] = 'Frontend design contract must not require the obsolete resources/views/pages/ hierarchy.';
}
if (str_contains($frontendDesignContract, '/development/design-system') === false) {
    $errors[] = 'Frontend design contract must point reusable pattern governance at /development/design-system.';
}

$designTokens = (string) file_get_contents($root.'/resources/css/tokens.css');
foreach ([
    '--admin-bg-page',
    '--admin-bg-surface',
    '--admin-bg-subtle',
    '--admin-text-primary',
    '--admin-text-secondary',
    '--admin-text-muted',
    '--admin-text-inverse',
    '--admin-primary',
    '--admin-success',
    '--admin-success-soft',
    '--admin-warning',
    '--admin-warning-soft',
    '--admin-danger',
    '--admin-danger-soft',
    '--admin-info',
    '--admin-info-soft',
    '--admin-border',
    '--admin-border-strong',
    '--admin-focus',
    '--admin-shadow-card',
    '--admin-shadow-float',
] as $adminToken) {
    if (str_contains($designTokens, $adminToken.':') === false) {
        $errors[] = "Semantic token contract is missing approved admin token [{$adminToken}].";
    }
}

$adminDashboard = (string) file_get_contents($root.'/resources/views/admin/dashboard.blade.php');
foreach (['slate-', 'amber-', 'emerald-'] as $rawAdminPalette) {
    if (str_contains($adminDashboard, $rawAdminPalette)) {
        $errors[] = "Admin dashboard bypasses semantic tokens with raw palette class [{$rawAdminPalette}].";
    }
}
foreach (['--admin-text-secondary', '--admin-border', '--admin-bg-subtle', '--admin-success', '--admin-warning'] as $requiredAdminUse) {
    if (str_contains($adminDashboard, $requiredAdminUse) === false) {
        $errors[] = "Admin dashboard must consume semantic token [{$requiredAdminUse}].";
    }
}

foreach ([
    'resources/views/home.blade.php',
    'resources/views/search/index.blade.php',
    'resources/views/entities/show.blade.php',
] as $representativePublicView) {
    $publicSource = (string) file_get_contents($root.'/'.$representativePublicView);
    if (str_contains($publicSource, 'bg-white')) {
        $errors[] = "Representative public view [{$representativePublicView}] bypasses --sc-bg-surface with bg-white.";
    }
    if (str_contains($publicSource, '--sc-bg-surface') === false) {
        $errors[] = "Representative public view [{$representativePublicView}] must consume --sc-bg-surface.";
    }
}

$chartSurface = (string) file_get_contents($root.'/resources/views/charts/show.blade.php');
foreach (['bg-white', '--sc-surface-subtle', 'emerald-', 'amber-', 'slate-'] as $chartDriftSignal) {
    if (str_contains($chartSurface, $chartDriftSignal)) {
        $errors[] = "Canonical chart surface contains non-semantic or invalid token signal [{$chartDriftSignal}].";
    }
}
foreach (['--sc-bg-surface', '--sc-bg-subtle', '--sc-success-soft', '--sc-warning-soft'] as $requiredChartToken) {
    if (str_contains($chartSurface, $requiredChartToken) === false) {
        $errors[] = "Canonical chart surface must consume semantic token [{$requiredChartToken}].";
    }
}

foreach ([
    'resources/views/admin/canonical-admissions/index.blade.php',
    'resources/views/admin/canonical-admissions/show.blade.php',
] as $representativeAdminReviewView) {
    $adminReviewSource = (string) file_get_contents($root.'/'.$representativeAdminReviewView);
    if (str_contains($adminReviewSource, 'slate-')) {
        $errors[] = "Representative admin review surface [{$representativeAdminReviewView}] bypasses semantic admin tokens with slate palette classes.";
    }
    foreach (['--admin-text-secondary', '--admin-border'] as $requiredReviewToken) {
        if (str_contains($adminReviewSource, $requiredReviewToken) === false) {
            $errors[] = "Representative admin review surface [{$representativeAdminReviewView}] must consume semantic token [{$requiredReviewToken}].";
        }
    }
}

$dataBoundary = json_decode(
    (string) file_get_contents($root.'/docs/project/domain/application-data-boundary.json'),
    true,
    512,
    JSON_THROW_ON_ERROR,
);

if (($dataBoundary['principles']['controllers_are_transport_adapters'] ?? false) !== true
    || ($dataBoundary['principles']['read_side_may_use_eloquent_or_query_builder'] ?? false) !== true
    || ($dataBoundary['principles']['write_side_may_use_transactions_and_persistence'] ?? false) !== true
) {
    $errors[] = 'Application data boundary contract is invalid.';
}

/** @return list<string> */
function recursivePhpFiles(string $directory): array
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
            $files[] = $file->getPathname();
        }
    }

    sort($files);

    return $files;
}

$controllerForbidden = array_values(array_filter(
    array_merge(
        (array) ($dataBoundary['controller_boundary']['forbidden_write_signals'] ?? []),
        (array) ($dataBoundary['controller_boundary']['forbidden_direct_read_signals'] ?? []),
    ),
    'is_string',
));
$controllerReadAllow = array_fill_keys(
    array_values(array_filter((array) ($dataBoundary['controller_boundary']['allowed_direct_read_files'] ?? []), 'is_string')),
    true,
);

foreach (recursivePhpFiles($root.'/app/Http/Controllers') as $path) {
    $relative = str_replace('\\', '/', substr($path, strlen($root) + 1));
    $contents = (string) file_get_contents($path);

    foreach ($controllerForbidden as $signal) {
        if ($signal === '' || ! str_contains($contents, $signal)) {
            continue;
        }

        if (isset($controllerReadAllow[$relative])
            && in_array($signal, (array) ($dataBoundary['controller_boundary']['forbidden_direct_read_signals'] ?? []), true)
        ) {
            continue;
        }

        $errors[] = "Controller data boundary violation [{$relative}] contains [{$signal}].";
    }
}

$readModelForbidden = array_values(array_filter(
    (array) ($dataBoundary['read_model_forbidden_signals'] ?? []),
    'is_string',
));

foreach ((array) ($dataBoundary['read_models'] ?? []) as $relative) {
    if (! is_string($relative) || ! is_file($root.'/'.$relative)) {
        $errors[] = "Registered read model is missing [{$relative}].";

        continue;
    }

    $contents = (string) file_get_contents($root.'/'.$relative);
    foreach ($readModelForbidden as $signal) {
        if ($signal !== '' && str_contains($contents, $signal)) {
            $errors[] = "Read model [{$relative}] must not mutate persistence via [{$signal}].";
        }
    }
}

$queryBudget = json_decode(
    (string) file_get_contents($root.'/docs/project/performance/query-budget-contract.json'),
    true,
    512,
    JSON_THROW_ON_ERROR,
);
if (($queryBudget['policy']['do_not_guess_hard_limits_without_representative_fixture'] ?? false) !== true) {
    $errors[] = 'Query budget authority must prohibit guessed limits without representative PostgreSQL fixtures.';
}

$readScaling = is_array($queryBudget['read_scaling'] ?? null) ? $queryBudget['read_scaling'] : [];
if (($readScaling['default_connection'] ?? null) !== 'pgsql'
    || ($readScaling['optional_read_connection'] ?? null) !== 'pgsql_read'
    || ($readScaling['automatic_read_routing'] ?? true) !== false
) {
    $errors[] = 'Read-scaling authority must keep pgsql primary-default and pgsql_read explicit-only.';
}

$allowedReadClassifications = ['primary_required', 'replica_eligible', 'insufficient_evidence'];
foreach ((array) ($queryBudget['surfaces'] ?? []) as $surface => $definition) {
    if (is_array($definition) === false) {
        $errors[] = "Query budget surface [{$surface}] must be an object.";

        continue;
    }

    $classification = $definition['read_scaling'] ?? null;
    if (is_string($classification) === false || in_array($classification, $allowedReadClassifications, true) === false) {
        $errors[] = "Query budget surface [{$surface}] has invalid read-scaling classification.";
    }

    $readModel = $definition['read_model'] ?? null;
    if (is_string($readModel) && $readModel !== '' && is_file($root.'/'.$readModel) === false) {
        $errors[] = "Query budget surface [{$surface}] references missing read model [{$readModel}].";
    }
}

$runtimeEnvironments = json_decode(
    (string) file_get_contents($root.'/docs/project/stack/runtime-environments.json'),
    true,
    512,
    JSON_THROW_ON_ERROR,
);
$databaseScaling = is_array($runtimeEnvironments['database_scaling'] ?? null) ? $runtimeEnvironments['database_scaling'] : [];
if (($databaseScaling['primary_connection'] ?? null) !== 'pgsql'
    || ($databaseScaling['optional_read_connection'] ?? null) !== 'pgsql_read'
    || ($databaseScaling['automatic_read_routing'] ?? true) !== false
    || ($databaseScaling['primary_is_authoritative'] ?? false) !== true
) {
    $errors[] = 'Runtime database-scaling authority must preserve explicit primary ownership and disabled automatic routing.';
}

$databaseConfig = (string) file_get_contents($root.'/config/database.php');
foreach ([
    "'default' => env('DB_CONNECTION', 'pgsql')",
    "'pgsql_read' => [",
    'DB_READ_HOST',
    'DB_READ_DATABASE',
    'DB_READ_USERNAME',
    'DB_READ_PASSWORD',
] as $requiredDatabaseToken) {
    if (str_contains($databaseConfig, $requiredDatabaseToken) === false) {
        $errors[] = "Database config is missing Stage 25 read-scaling token [{$requiredDatabaseToken}].";
    }
}

if ($errors !== []) {
    fwrite(STDERR, implode(PHP_EOL, $errors).PHP_EOL);
    exit(1);
}
echo "Architecture conformance verification passed.\n";
