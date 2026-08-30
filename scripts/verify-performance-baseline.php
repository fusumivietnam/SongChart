<?php

declare(strict_types=1);

use App\Support\Engineering\RepositoryContractResolver;

$root = dirname(__DIR__);
require_once $root.'/app/Support/Engineering/RepositoryContractResolver.php';
$errors = [];
$contractPath = $root.'/docs/project/performance/performance-contracts.json';
try {
    $contract = json_decode((string) file_get_contents($contractPath), true, flags: JSON_THROW_ON_ERROR);
} catch (Throwable $exception) {
    fwrite(STDERR, 'Performance baseline verification failed: '.$exception->getMessage().PHP_EOL);
    exit(1);
}

$publicFrontend = is_array($contract['public_frontend_release'] ?? null) ? $contract['public_frontend_release'] : [];
$expectedPublicFrontend = [
    'third_party_blocking_assets' => false,
    'images_require_dimensions' => true,
    'reduced_motion_required' => true,
    'mobile_safe_area_required' => true,
];
foreach ($expectedPublicFrontend as $rule => $expected) {
    if (($publicFrontend[$rule] ?? null) !== $expected) {
        $errors[] = "Public frontend performance contract must declare {$rule}.";
    }
}

$publicViewRoots = [
    'resources/views/home.blade.php',
    'resources/views/layouts/frontend.blade.php',
    'resources/views/search',
    'resources/views/entities',
    'resources/views/errors',
    'resources/views/components/search',
    'resources/views/components/shell',
    'resources/views/components/entity',
    'resources/views/components/provider',
];
$publicViewFiles = [];
foreach ($publicViewRoots as $relative) {
    $path = $root.'/'.$relative;
    if (is_file($path)) {
        $publicViewFiles[] = $path;

        continue;
    }
    if (! is_dir($path)) {
        continue;
    }
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS));
    foreach ($iterator as $file) {
        if ($file->isFile() && str_ends_with($file->getFilename(), '.blade.php')) {
            $publicViewFiles[] = $file->getPathname();
        }
    }
}

foreach (array_unique($publicViewFiles) as $path) {
    $source = (string) file_get_contents($path);
    $relative = str_replace($root.'/', '', $path);
    if (preg_match('/<(?:script|link)\b[^>]+(?:src|href)=["\']https?:\/\//i', $source) === 1) {
        $errors[] = "{$relative} must not add third-party blocking script or stylesheet assets.";
    }
    if (preg_match_all('/<img\b[^>]*>/i', $source, $matches) === false) {
        continue;
    }
    foreach ($matches[0] ?? [] as $imageTag) {
        if (preg_match('/\bwidth\s*=/i', $imageTag) !== 1 || preg_match('/\bheight\s*=/i', $imageTag) !== 1) {
            $errors[] = "{$relative} contains an image without explicit width and height attributes.";
        }
    }
}

$frontendCss = (string) file_get_contents($root.'/resources/css/app.css');
if (! str_contains($frontendCss, '@media (prefers-reduced-motion: reduce)')) {
    $errors[] = 'Public frontend CSS must preserve reduced-motion handling.';
}
if (! str_contains($frontendCss, 'env(safe-area-inset-bottom)')) {
    $errors[] = 'Public frontend CSS must preserve mobile safe-area spacing.';
}

$useCases = is_array($contract['use_cases'] ?? null) ? $contract['use_cases'] : [];
foreach (['admin.providers.index', 'admin.imports.index', 'admin.quarantine.index', 'admin.identity-conflicts.index'] as $key) {
    $entry = $useCases[$key] ?? null;
    if (! is_array($entry) || ! is_int($entry['query_budget'] ?? null) || $entry['query_budget'] < 1) {
        $errors[] = "Missing positive query budget for {$key}.";
    }
    if (($entry['pagination'] ?? null) !== 'length_aware' || ($entry['per_page'] ?? null) !== 25) {
        $errors[] = "{$key} must declare length-aware pagination at 25 rows.";
    }
}

$appProvider = (string) file_get_contents($root.'/app/Providers/AppServiceProvider.php');
if (! str_contains($appProvider, 'Model::shouldBeStrict(! $this->app->isProduction())')) {
    $errors[] = 'AppServiceProvider must keep strict Eloquent enabled outside production.';
}

$adminReadModels = [
    'app/Support/Admin/AdminDashboardSnapshot.php',
    'app/Support/Admin/AdminInformationArchitecture.php',
    'app/Support/Admin/CatalogAdministration.php',
    'app/Support/Admin/ProviderOperationsConsole.php',
];
foreach ($adminReadModels as $relative) {
    $source = (string) file_get_contents($root.'/'.$relative);
    if (str_contains($source, 'Schema::hasTable(') || str_contains($source, 'Facades\\Schema')) {
        $errors[] = "{$relative} must not probe schema existence on request hot paths.";
    }
}

$providerConsole = (string) file_get_contents($root.'/app/Support/Admin/ProviderOperationsConsole.php');
if (preg_match('/->paginate\(25\)/', $providerConsole) !== 1) {
    $errors[] = 'ProviderOperationsConsole list use cases must remain paginated.';
}
foreach (['->limit(25)', '->limit(50)'] as $needle) {
    if (! str_contains($providerConsole, $needle)) {
        $errors[] = "ProviderOperationsConsole must keep bounded detail collections using {$needle}.";
    }
}
if (preg_match('/->get\(\s*\)/', $providerConsole) === 1 && ! str_contains($providerConsole, "->limit(25)\n            ->get()")) {
    $errors[] = 'ProviderOperationsConsole contains an unbounded get() call.';
}

$identityConsole = (string) file_get_contents($root.'/app/Support/Admin/IdentityConflictReviewConsole.php');
if (! str_contains($identityConsole, "getRawOriginal('entity_type')")) {
    $errors[] = 'IdentityConflictReviewConsole must normalize entity_type from the persisted scalar boundary.';
}
if (str_contains($identityConsole, "(string) \$entity->getAttribute('verification_state')")) {
    $errors[] = 'IdentityConflictReviewConsole must not cast backed enum objects directly to string.';
}

foreach (['admin.dashboard', 'admin.catalog.entities.index', 'admin.catalog.entities.show'] as $key) {
    $entry = $useCases[$key] ?? null;
    if (! is_array($entry) || ! is_int($entry['query_budget'] ?? null) || $entry['query_budget'] < 1) {
        $errors[] = "Missing positive query budget for {$key}.";
    }
}

$catalogAdmin = (string) file_get_contents($root.'/app/Support/Admin/CatalogAdministration.php');
if (substr_count($catalogAdmin, '->limit(100)') < 3) {
    $errors[] = 'CatalogAdministration detail support collections must all remain bounded at 100 rows.';
}

$composer = json_decode((string) file_get_contents($root.'/composer.json'), true);
$scripts = is_array($composer['scripts'] ?? null) ? $composer['scripts'] : [];
if (($scripts['performance:verify'] ?? null) !== '@php scripts/verify-performance-baseline.php') {
    $errors[] = 'composer.json must define performance:verify.';
}
if (($scripts['environment:verify'] ?? null) !== '@php scripts/verify-runtime-environment.php --release') {
    $errors[] = 'composer.json must define environment:verify.';
}
$quality = $scripts['quality:verify'] ?? [];
if (! is_array($quality) || ! in_array('@performance:verify', $quality, true)) {
    $errors[] = 'quality:verify must execute @performance:verify.';
}
$stage = $scripts['stage:verify'] ?? [];
$canonical = $scripts['canonical:verify'] ?? [];
try {
    $releaseContract = (new RepositoryContractResolver($root))->value('release-pipeline');
} catch (Throwable $exception) {
    $errors[] = 'Release pipeline contract is invalid: '.$exception->getMessage();
    $releaseContract = [];
}

if (! is_array($stage) || $stage !== ($releaseContract['stage_steps'] ?? null)) {
    $errors[] = 'stage:verify must match the shared stage verification topology.';
}
if (! is_array($canonical) || $canonical !== ($releaseContract['canonical_steps'] ?? null)) {
    $errors[] = 'canonical:verify must match the shared canonical verification topology.';
}
if (array_key_exists('release:verify', $scripts) || array_key_exists('verify', $scripts)) {
    $errors[] = 'Legacy verification aliases must remain removed.';
}
if ($errors !== []) {
    fwrite(STDERR, "Performance baseline verification failed:\n- ".implode("\n- ", array_unique($errors))."\n");
    exit(1);
}

fwrite(STDOUT, "Performance baseline verification passed.\n");
