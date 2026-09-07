<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$errors = [];

$decode = static function (string $relative) use ($root, &$errors): array {
    try {
        $decoded = json_decode((string) file_get_contents($root.'/'.$relative), true, flags: JSON_THROW_ON_ERROR);
    } catch (Throwable $exception) {
        $errors[] = "Unable to decode {$relative}: {$exception->getMessage()}";

        return [];
    }

    return is_array($decoded) ? $decoded : [];
};

$domain = $decode('docs/project/domain/domain-contracts.json');
$operational = $decode('docs/project/domain/operational-contracts.json');
$registry = $decode('docs/project/domain/use-case-contracts.json');
$journeys = $decode('docs/project/domain/product-user-journeys.json');
$routes = (string) file_get_contents($root.'/routes/web.php');
$domainEntities = is_array($domain['entities'] ?? null) ? $domain['entities'] : [];
$operationalSurfaces = is_array($operational['surfaces'] ?? null) ? $operational['surfaces'] : [];
$useCases = is_array($registry['use_cases'] ?? null) ? $registry['use_cases'] : [];
$allowedMethods = ['GET', 'POST', 'PUT', 'PATCH', 'DELETE'];
$adminMiddleware = ['web', 'auth', 'active', 'verified', 'can:access-admin', 'two-factor.confirmed'];

if (($registry['schema_version'] ?? null) !== 1) {
    $errors[] = 'Use-case contract schema_version must be 1.';
}

foreach ($useCases as $key => $contract) {
    if (! is_string($key) || ! is_array($contract)) {
        $errors[] = 'Every use-case contract must be a keyed object.';

        continue;
    }

    foreach (['method', 'route_name', 'uri', 'middleware', 'implementation', 'actors', 'read_only', 'output_type', 'data_surfaces'] as $required) {
        if (! array_key_exists($required, $contract)) {
            $errors[] = "Use-case [{$key}] is missing [{$required}].";
        }
    }

    $method = $contract['method'] ?? null;
    $routeName = $contract['route_name'] ?? null;
    $uri = $contract['uri'] ?? null;
    if (! is_string($method) || ! in_array($method, $allowedMethods, true)) {
        $errors[] = "Use-case [{$key}] has an unsupported HTTP method.";
    }
    if (! is_string($routeName) || $routeName === '' || ! is_string($uri) || $uri === '') {
        $errors[] = "Use-case [{$key}] must declare route_name and uri.";
    } else {
        $suffix = str_starts_with($routeName, 'admin.') ? substr($routeName, 6) : $routeName;
        $routeUri = str_starts_with($routeName, 'admin.') ? preg_replace('#^/admin#', '', $uri) : $uri;
        $verb = strtolower((string) $method);
        $needle = "Route::{$verb}('{$routeUri}'";
        if (! str_contains($routes, $needle) || ! str_contains($routes, "->name('{$suffix}')")) {
            $errors[] = "Use-case [{$key}] route implementation does not match {$method} {$uri} / {$routeName}.";
        }
    }

    $middleware = $contract['middleware'] ?? null;
    if (! is_array($middleware) || array_filter($middleware, 'is_string') !== $middleware) {
        $errors[] = "Use-case [{$key}] middleware must be a list of strings.";
    } elseif (str_starts_with((string) $routeName, 'admin.')) {
        if (array_slice($middleware, 0, count($adminMiddleware)) !== $adminMiddleware) {
            $errors[] = "Admin use-case [{$key}] must begin with the canonical admin middleware chain.";
        }
        $adminNeedle = "Route::middleware(['auth', 'active', 'verified', 'can:access-admin', 'two-factor.confirmed'])";
        if (! str_contains($routes, $adminNeedle)) {
            $errors[] = 'routes/web.php is missing the canonical admin middleware group.';
        }
    } elseif ($middleware !== ['web']) {
        $errors[] = "Public web use-case [{$key}] must declare implicit web middleware.";
    }

    $implementation = $contract['implementation'] ?? null;
    if (! is_string($implementation) || ! is_file($root.'/'.$implementation)) {
        $errors[] = "Use-case [{$key}] implementation file is missing.";
    } elseif (! str_contains((string) file_get_contents($root.'/'.$implementation), "'{$key}'")) {
        $errors[] = "Use-case [{$key}] implementation must declare its contract key.";
    }

    $actors = $contract['actors'] ?? null;
    if (! is_array($actors) || $actors === [] || array_filter($actors, 'is_string') !== $actors) {
        $errors[] = "Use-case [{$key}] actors must be a non-empty list of strings.";
    }

    $surfaces = $contract['data_surfaces'] ?? null;
    if (! is_array($surfaces)) {
        $errors[] = "Use-case [{$key}] data_surfaces must be an object.";
        $surfaces = [];
    }
    foreach ($surfaces as $entityType => $surface) {
        if (! is_string($entityType) || ! isset($domainEntities[$entityType]) || ! is_array($surface)) {
            $errors[] = "Use-case [{$key}] references unknown entity surface [{$entityType}].";

            continue;
        }
        $declaredFields = array_keys(is_array($domainEntities[$entityType]['fields'] ?? null) ? $domainEntities[$entityType]['fields'] : []);
        foreach (['read_fields', 'write_fields'] as $listName) {
            $fieldList = $surface[$listName] ?? null;
            if (! is_array($fieldList)) {
                $errors[] = "Use-case [{$key}.{$entityType}] must declare {$listName} as a list.";

                continue;
            }
            foreach ($fieldList as $field) {
                if (! is_string($field) || ! in_array($field, $declaredFields, true)) {
                    $errors[] = "Use-case [{$key}.{$entityType}] {$listName} references undeclared field [{$field}].";
                }
            }
        }
        if (($contract['read_only'] ?? false) === true && ($surface['write_fields'] ?? []) !== []) {
            $errors[] = "Read-only use-case [{$key}] declares writes for [{$entityType}].";
        }
    }

    foreach (($contract['support_surfaces'] ?? []) as $surfaceKey => $fields) {
        $surface = $operationalSurfaces[$surfaceKey] ?? null;
        if (! is_string($surfaceKey) || ! is_array($surface) || ! is_array($fields)) {
            $errors[] = "Use-case [{$key}] references unknown operational surface [{$surfaceKey}].";

            continue;
        }
        $allowed = $surface['fields'] ?? [];
        foreach ($fields as $field) {
            if (! is_string($field) || ! in_array($field, $allowed, true)) {
                $errors[] = "Use-case [{$key}.{$surfaceKey}] references undeclared operational field [{$field}].";
            }
        }
    }

    foreach (($contract['support_surface_writes'] ?? []) as $surfaceKey => $fields) {
        $surface = $operationalSurfaces[$surfaceKey] ?? null;
        if (! is_string($surfaceKey) || ! is_array($surface) || ! is_array($fields)) {
            $errors[] = "Use-case [{$key}] references unknown operational write surface [{$surfaceKey}].";

            continue;
        }
        if (($contract['read_only'] ?? false) === true && $fields !== []) {
            $errors[] = "Read-only use-case [{$key}] declares operational writes for [{$surfaceKey}].";
        }
        $allowed = $surface['fields'] ?? [];
        foreach ($fields as $field) {
            if (! is_string($field) || ! in_array($field, $allowed, true)) {
                $errors[] = "Use-case [{$key}.{$surfaceKey}] writes undeclared operational field [{$field}].";
            }
        }
    }

    foreach (($contract['route_parameters'] ?? []) as $parameter => $type) {
        if (! is_string($parameter) || ! is_string($type) || ! str_contains((string) $uri, '{'.$parameter.'}')) {
            $errors[] = "Use-case [{$key}] route parameter [{$parameter}] does not match its URI.";
        }
        if (! in_array($type, ['entity_type', 'ulid', 'slug', 'string'], true)) {
            $errors[] = "Use-case [{$key}] route parameter [{$parameter}] has unsupported type [{$type}].";
        }
    }
}

if (($journeys['schema_version'] ?? null) !== 1 || ($journeys['owner'] ?? null) !== 'product-user-journey-authority') {
    $errors[] = 'Product/user journey authority must use schema_version 1 and the canonical owner.';
}

$allowedJourneyStatuses = ['implemented-foundation', 'foundation-partial', 'surface-implemented-contract-gap', 'planned'];
foreach (($journeys['journeys'] ?? []) as $journeyKey => $journey) {
    if (! is_string($journeyKey) || ! is_array($journey)) {
        $errors[] = 'Every product/user journey must be a keyed object.';

        continue;
    }

    foreach (['actor', 'status', 'goal', 'required_domain_capabilities', 'stage_20_gaps'] as $required) {
        if (! array_key_exists($required, $journey)) {
            $errors[] = "Journey [{$journeyKey}] is missing [{$required}].";
        }
    }

    if (! is_string($journey['actor'] ?? null) || ($journey['actor'] ?? '') === '') {
        $errors[] = "Journey [{$journeyKey}] must declare a non-empty actor.";
    }
    if (! in_array($journey['status'] ?? null, $allowedJourneyStatuses, true)) {
        $errors[] = "Journey [{$journeyKey}] has an unsupported status.";
    }
    if (! is_array($journey['required_domain_capabilities'] ?? null) || ($journey['required_domain_capabilities'] ?? []) === []) {
        $errors[] = "Journey [{$journeyKey}] must declare required domain capabilities.";
    }
    if (! is_array($journey['stage_20_gaps'] ?? null)) {
        $errors[] = "Journey [{$journeyKey}] stage_20_gaps must be a list.";
    }

    foreach (($journey['use_cases'] ?? []) as $useCaseKey) {
        if (! is_string($useCaseKey) || ! array_key_exists($useCaseKey, $useCases)) {
            $errors[] = "Journey [{$journeyKey}] references unregistered executable use case [{$useCaseKey}].";
        }
    }

    foreach (($journey['use_case_prefixes'] ?? []) as $prefix) {
        if (! is_string($prefix) || $prefix === '') {
            $errors[] = "Journey [{$journeyKey}] has an invalid use-case prefix.";

            continue;
        }
        $matches = array_filter(array_keys($useCases), static fn (string $key): bool => str_starts_with($key, $prefix));
        if ($matches === []) {
            $errors[] = "Journey [{$journeyKey}] use-case prefix [{$prefix}] matches no executable use case.";
        }
    }

    foreach (($journey['route_names'] ?? []) as $routeName) {
        if (! is_string($routeName) || ! str_contains($routeName, '.')) {
            $errors[] = "Journey [{$journeyKey}] has an invalid route-only surface.";

            continue;
        }
        [$group, $suffix] = explode('.', $routeName, 2);
        if (! str_contains($routes, "->name('{$group}.')") || ! str_contains($routes, "->name('{$suffix}')")) {
            $errors[] = "Journey [{$journeyKey}] route-only surface [{$routeName}] is not present in routes/web.php.";
        }
    }
}

if ($errors !== []) {
    fwrite(STDERR, "Use-case contract verification failed:\n- ".implode("\n- ", array_unique($errors))."\n");
    exit(1);
}

fwrite(STDOUT, "Use-case contract verification passed.\n");
