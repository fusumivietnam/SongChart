<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$authorityPath = $root.'/docs/project/domain/route-authority.json';
$routesPath = $root.'/routes/web.php';
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
        if (! str_contains($routes, "->name('index')")) {
            $errors[] = 'Canonical development design-system index route is missing.';
        }

        continue;
    }
    if (! str_contains($routes, "->name('{$suffix}')")) {
        $errors[] = "Canonical design-system route name is missing: {$name}.";
    }
}

$viewRoots = [$root.'/resources/views', $root.'/tests'];
foreach ($viewRoots as $viewRoot) {
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($viewRoot, FilesystemIterator::SKIP_DOTS));
    foreach ($iterator as $file) {
        if (! $file->isFile() || ! preg_match('/\.(php|blade\.php)$/', $file->getFilename())) {
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
