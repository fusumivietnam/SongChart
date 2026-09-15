<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$authorityPath = $root.'/docs/project/domain/route-authority.json';
$routesPath = $root.'/routes/web.php';
$configPath = $root.'/config/songchart.php';
$errors = [];

try {
    $authority = json_decode((string) file_get_contents($authorityPath), true, flags: JSON_THROW_ON_ERROR);
} catch (Throwable $e) {
    fwrite(STDERR, 'Route authority verification failed: '.$e->getMessage().PHP_EOL);
    exit(1);
}

$routes = (string) file_get_contents($routesPath);
foreach ($authority['retired_aliases'] ?? [] as $alias) {
    $literal = str_replace('{section?}', '', str_replace('{concept}', '', (string) $alias));
    $literal = rtrim($literal, '/');
    if ($literal !== '' && str_contains($routes, "'{$literal}")) {
        $errors[] = "Retired route alias is still registered: {$alias}.";
    }
}
foreach ($authority['retired_route_names'] ?? [] as $name) {
    if (preg_match("/->name\\('".preg_quote((string) $name, '/')."'\\)/", $routes) === 1) {
        $errors[] = "Retired route name is still registered: {$name}.";
    }
}
foreach ($authority['canonical_design_system_route_names'] ?? [] as $name) {
    $suffix = str_replace('development.design-system.', '', (string) $name);
    if ($suffix === 'index') {
        if (str_contains($routes, "->name('index')") === false) {
            $errors[] = 'Canonical development design-system index route is missing.';
        }

        continue;
    }
    if (str_contains($routes, "->name('{$suffix}')") === false) {
        $errors[] = "Canonical design-system route name is missing: {$name}.";
    }
}

$edge = $authority['edge_delivery'] ?? null;
if (is_array($edge) === false) {
    $errors[] = 'Edge delivery policy is missing from route authority.';
} else {
    if (($edge['provider_mode'] ?? null) !== 'provider_neutral') {
        $errors[] = 'Edge delivery policy must remain provider_neutral.';
    }
    if (($edge['fallback'] ?? null) !== 'direct_dns_to_caddy') {
        $errors[] = 'Edge delivery fallback must remain direct_dns_to_caddy.';
    }
    if (($edge['automatic_infrastructure_mutation'] ?? null) !== false) {
        $errors[] = 'Edge delivery policy must not enable automatic infrastructure mutation.';
    }

    $methods = $edge['shared_cache_methods'] ?? [];
    sort($methods);
    if ($methods !== ['GET', 'HEAD']) {
        $errors[] = 'Shared edge cache methods must be exactly GET and HEAD.';
    }

    $protectedPrefixes = ['/account', '/admin', '/development', '/search'];
    $classes = $edge['classes'] ?? [];
    foreach ($classes as $className => $class) {
        if (is_array($class) === false) {
            $errors[] = "Edge delivery class {$className} must be an object.";

            continue;
        }

        $sharedCache = $class['shared_cache'] ?? null;
        $patterns = $class['patterns'] ?? [];
        if (in_array($sharedCache, ['eligible', 'eligible_if_anonymous', 'bypass'], true) === false) {
            $errors[] = "Edge delivery class {$className} has an invalid shared_cache mode.";
        }

        if ($sharedCache !== 'bypass') {
            foreach ($patterns as $pattern) {
                foreach ($protectedPrefixes as $prefix) {
                    if ($pattern === $prefix || str_starts_with((string) $pattern, $prefix.'/') || str_starts_with((string) $pattern, $prefix.'*')) {
                        $errors[] = "Protected route pattern {$pattern} must not be shared-cache eligible.";
                    }
                }
            }

            foreach (['edge_ttl_seconds', 'invalidation'] as $required) {
                if (array_key_exists($required, $class) === false) {
                    $errors[] = "Shared-cache class {$className} is missing {$required}.";
                }
            }
            if (isset($class['edge_ttl_seconds']) && (is_int($class['edge_ttl_seconds']) === false || $class['edge_ttl_seconds'] < 0)) {
                $errors[] = "Shared-cache class {$className} has an invalid edge_ttl_seconds value.";
            }
            if (($class['invalidation'] ?? []) === []) {
                $errors[] = "Shared-cache class {$className} must declare invalidation semantics.";
            }
        }
    }

    $privateClass = $classes['private_or_mutating'] ?? [];
    $privatePatterns = $privateClass['patterns'] ?? [];
    foreach (['/account*', '/admin*', '/development*'] as $requiredPattern) {
        if (in_array($requiredPattern, $privatePatterns, true) === false) {
            $errors[] = "Private edge bypass is missing {$requiredPattern}.";
        }
    }
    if (($privateClass['shared_cache'] ?? null) !== 'bypass') {
        $errors[] = 'Private/authenticated route family must bypass shared cache.';
    }

    $searchClass = $classes['dynamic_search'] ?? [];
    if (($searchClass['shared_cache'] ?? null) !== 'bypass' || in_array('/search*', $searchClass['patterns'] ?? [], true) === false) {
        $errors[] = 'Dynamic search must bypass shared cache until a bounded cache-key policy is accepted.';
    }

    $bypass = $edge['mandatory_bypass'] ?? [];
    if (in_array('Authorization', $bypass['request_headers_present'] ?? [], true) === false) {
        $errors[] = 'Authorization-header requests must bypass shared cache.';
    }
    if (in_array('songchart_session', $bypass['cookies_present'] ?? [], true) === false) {
        $errors[] = 'Session-cookie requests must bypass shared cache.';
    }
    if (in_array('Set-Cookie', $bypass['response_headers'] ?? [], true) === false) {
        $errors[] = 'Set-Cookie responses must bypass shared cache.';
    }

    $threshold = $edge['decision_thresholds']['investigate_edge_delivery'] ?? [];
    $signals = array_column($threshold['stage_24_signals_any'] ?? [], 'metric');
    foreach (['pulse_slow_events_15m', 'cache_hit_ratio'] as $metric) {
        if (in_array($metric, $signals, true) === false) {
            $errors[] = "Edge investigation threshold must consume Stage 24 metric {$metric}.";
        }
    }
    foreach (['public_request_volume_baseline', 'public_p95_latency_baseline', 'origin_request_rate_baseline'] as $requiredEvidence) {
        if (in_array($requiredEvidence, $threshold['required_before_adoption'] ?? [], true) === false) {
            $errors[] = "Edge adoption evidence is missing {$requiredEvidence}.";
        }
    }

    $config = (string) file_get_contents($configPath);
    foreach ($signals as $metric) {
        if (str_contains($config, "'{$metric}'") === false) {
            $errors[] = "Edge decision references unknown operational metric {$metric}.";
        }
    }
}

$viewRoots = [$root.'/resources/views', $root.'/tests'];
foreach ($viewRoots as $viewRoot) {
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($viewRoot, FilesystemIterator::SKIP_DOTS));
    foreach ($iterator as $file) {
        if ($file->isFile() === false || preg_match('/\.(php|blade\.php)$/', $file->getFilename()) !== 1) {
            continue;
        }
        $source = (string) file_get_contents($file->getPathname());
        foreach ($authority['retired_route_names'] ?? [] as $name) {
            if (str_contains($source, "route('{$name}'") || str_contains($source, 'route("'.$name.'"')) {
                $relative = str_replace('\\', '/', substr($file->getPathname(), strlen($root) + 1));
                $errors[] = "Retired route name {$name} is referenced by {$relative}.";
            }
        }
    }
}

if ($errors !== []) {
    fwrite(STDERR, "Route authority verification failed:\n- ".implode("\n- ", array_unique($errors)).PHP_EOL);
    exit(1);
}

fwrite(STDOUT, 'Route authority verification passed.'.PHP_EOL);
