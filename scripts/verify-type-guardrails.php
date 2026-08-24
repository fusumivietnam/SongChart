<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$errors = [];

$requiredReadonlyDtos = [
    'app/Support/DomainContracts/DTO/EntityDataSurface.php',
    'app/Support/DomainContracts/DTO/SupportDataSurface.php',
    'app/Support/DomainContracts/DTO/UseCaseContract.php',
];
foreach ($requiredReadonlyDtos as $relative) {
    $source = (string) @file_get_contents($root.'/'.$relative);
    if ($source === '') {
        $errors[] = "Missing typed contract DTO: {$relative}";

        continue;
    }
    if (! str_contains($source, 'final readonly class ')) {
        $errors[] = "Typed contract DTO must be final readonly: {$relative}";
    }
    if (str_contains($source, 'public mixed $')) {
        $errors[] = "Typed contract DTO must not expose mixed properties: {$relative}";
    }
}

$catalog = (string) file_get_contents($root.'/app/Support/Admin/CatalogAdministration.php');
if (! str_contains($catalog, 'private readonly DomainContractRegistry $contracts')) {
    $errors[] = 'CatalogAdministration must depend on DomainContractRegistry.';
}
foreach (["'title' => 'name'", "'title' => 'title'"] as $forbidden) {
    if (str_contains($catalog, $forbidden)) {
        $errors[] = "CatalogAdministration contains forbidden inferred display-field mapping [{$forbidden}].";
    }
}
if (! str_contains($catalog, '$this->contracts->displayField(')) {
    $errors[] = 'CatalogAdministration must consume display fields from DomainContractRegistry.';
}

$search = (string) file_get_contents($root.'/app/Support/Search/EloquentSearchCatalog.php');
if (! str_contains($search, '$this->contracts->displayField($type)')) {
    $errors[] = 'EloquentSearchCatalog must consume display fields from DomainContractRegistry.';
}

$useCaseRegistry = (string) file_get_contents($root.'/app/Support/DomainContracts/UseCaseContractRegistry.php');
foreach (['UseCaseContract', 'EntityDataSurface', 'SupportDataSurface', 'middleware:', 'uri:', 'implementation:', 'JSON_THROW_ON_ERROR'] as $needle) {
    if (! str_contains($useCaseRegistry, $needle)) {
        $errors[] = "UseCaseContractRegistry is missing typed boundary [{$needle}].";
    }
}

$controllers = [
    'app/Http/Controllers/Search/SearchController.php' => ['public.search'],
    'app/Http/Controllers/Admin/CatalogController.php' => ['admin.catalog.index', 'admin.catalog.show', 'admin.catalog.artist.update'],
    'app/Http/Controllers/Admin/OperationsController.php' => ['admin.providers.index', 'admin.imports.index', 'admin.quarantine.index', 'admin.users.index'],
    'app/Http/Controllers/Admin/IdentityConflictReviewController.php' => ['admin.identity-conflicts.index', 'admin.identity-conflicts.show', 'admin.identity-conflicts.decide'],
];
foreach ($controllers as $relative => $keys) {
    $source = (string) file_get_contents($root.'/'.$relative);
    foreach ($keys as $key) {
        if (! str_contains($source, "'{$key}'")) {
            $errors[] = "Implementation [{$relative}] must declare use-case key [{$key}].";
        }
    }
}

$phpstan = (string) file_get_contents($root.'/phpstan.neon');
if (str_contains($phpstan, 'ignoreErrors:') || str_contains($phpstan, 'baseline')) {
    $errors[] = 'Type guardrails must not be implemented by adding PHPStan ignores or a baseline.';
}

if ($errors !== []) {
    fwrite(STDERR, "Type-guardrail verification failed:\n- ".implode("\n- ", array_unique($errors))."\n");
    exit(1);
}

fwrite(STDOUT, "Type-guardrail verification passed.\n");
