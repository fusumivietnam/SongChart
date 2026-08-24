<?php

declare(strict_types=1);

it('defines a port-safe Docker local development HTTPS profile', function (): void {
    $root = dirname(__DIR__, 2);
    $compose = (string) file_get_contents($root.'/compose.dev.yml');
    $caddy = (string) file_get_contents($root.'/docker/dev/Caddyfile');

    expect($compose)
        ->toContain('127.0.0.1:8080:80')
        ->toContain('127.0.0.1:8443:443')
        ->toContain('postgres:18.4-bookworm')
        ->toContain('songchart_dev_pgdata:/var/lib/postgresql')
        ->toContain('caddy:2.11.3-alpine')
        ->toContain('songchart_dev_vendor:/workspace/vendor')
        ->toContain('MUSICBRAINZ_ENABLED: "${MUSICBRAINZ_ENABLED:-false}"')
        ->toContain('REDIS_CACHE_CONNECTION: cache')
        ->toContain('REDIS_CACHE_DB: "1"')
        ->toContain('command: ["sh", "-lc", "cd public && exec php -S 0.0.0.0:8000 ../vendor/laravel/framework/src/Illuminate/Foundation/resources/server.php"]')
        ->not->toContain('command: ["php", "artisan", "serve"')
        ->toContain('--queue=critical,discovery-projections,provider-health,provider-imports,provider-normalization,notifications,default')
        ->and($caddy)
        ->toContain('docker.songchart.test')
        ->toContain('reverse_proxy app:8000');
});

it('keeps Docker trusted proxy handling local-only and opt-in', function (): void {
    $root = dirname(__DIR__, 2);
    $bootstrap = (string) file_get_contents($root.'/bootstrap/app.php');

    expect($bootstrap)
        ->toContain("env('APP_ENV') === 'local'")
        ->toContain("env('SONGCHART_TRUST_DOCKER_PROXY', false)")
        ->toContain("\$middleware->trustProxies(at: '*')");
});

it('provides an idempotent Docker dev readiness and verification cycle', function (): void {
    $root = dirname(__DIR__, 2);
    $cli = (string) file_get_contents($root.'/scripts/songchart.ps1');
    $ready = (string) file_get_contents($root.'/scripts/prepare-docker-dev.ps1');

    expect($cli)
        ->toContain("'ready'")
        ->toContain("'cycle'")
        ->toContain('songchart-verify')
        ->toContain('run-database-tests.php postgres --prepare-schema')
        ->and($ready)
        ->toContain('ALTER ROLE songchart_docker')
        ->toContain('php', 'artisan', 'migrate')
        ->toContain('curl.exe --fail --silent --show-error')
        ->toContain('/up')
        ->toContain('Live application smoke failed');
});
