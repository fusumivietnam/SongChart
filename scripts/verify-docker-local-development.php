<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$errors = [];

$required = [
    'compose.dev.yml',
    'compose.codespaces.yml',
    '.env.docker.example',
    'docker/dev/Caddyfile',
    'docker-dev-setup.bat',
    'docker-dev-up.bat',
    'docker-dev-down.bat',
    'scripts/setup-docker-dev.ps1',
    'scripts/docker-dev-up.ps1',
    'scripts/docker-dev-down.ps1',
    'scripts/setup-docker-dev.sh',
    'songchart',
    'songchart.bat',
    'scripts/songchart.ps1',
    'docs/project/stack/docker-development-contract.json',
];

foreach ($required as $relative) {
    if (! is_file($root.'/'.$relative)) {
        $errors[] = "Missing Docker local-development file [{$relative}].";
    }
}

$compose = (string) file_get_contents($root.'/compose.dev.yml');
foreach ([
    'image: postgres:18.4-bookworm',
    'image: redis:7.4-alpine',
    'image: caddy:2.11.3-alpine',
    '127.0.0.1:8080:80',
    '127.0.0.1:8443:443',
    'songchart_dev_pgdata:/var/lib/postgresql',
    'songchart_dev_vendor:/workspace/vendor',
    'condition: service_healthy',
    'SONGCHART_TRUST_DOCKER_PROXY: "true"',
    'MUSICBRAINZ_ENABLED: "${MUSICBRAINZ_ENABLED:-false}"',
    'command: ["sh", "-lc", "cd public && exec php -S 0.0.0.0:8000 ../vendor/laravel/framework/src/Illuminate/Foundation/resources/server.php"]',
    '--queue=critical,discovery-projections,provider-health,provider-imports,provider-normalization,notifications,default',
] as $signal) {
    if (! str_contains($compose, $signal)) {
        $errors[] = "compose.dev.yml missing [{$signal}].";
    }
}

if (str_contains($compose, 'command: ["php", "artisan", "serve"')) {
    $errors[] = 'Docker app runtime must not use Laravel ServeCommand; launch the PHP built-in server directly so Docker environment variables reach the HTTP process unchanged.';
}

if (str_contains($compose, '443:443') && ! str_contains($compose, '127.0.0.1:8443:443')) {
    $errors[] = 'Docker local development must not claim host port 443 by default.';
}

$codespacesCompose = (string) file_get_contents($root.'/compose.codespaces.yml');
foreach ([
    'APP_URL: "${SONGCHART_CODESPACES_APP_URL}"',
    'SESSION_DOMAIN: ""',
    'SESSION_SECURE_COOKIE: "true"',
    '127.0.0.1:8000:8000',
] as $signal) {
    if (! str_contains($codespacesCompose, $signal)) {
        $errors[] = "compose.codespaces.yml missing [{$signal}].";
    }
}
if (str_contains($codespacesCompose, 'caddy:')) {
    $errors[] = 'Codespaces override must not create a second Caddy/TLS runtime.';
}

$caddy = (string) file_get_contents($root.'/docker/dev/Caddyfile');
foreach ([
    'docker.songchart.test',
    'tls /certs/docker.songchart.test.pem /certs/docker.songchart.test-key.pem',
    'reverse_proxy app:8000',
] as $signal) {
    if (! str_contains($caddy, $signal)) {
        $errors[] = "Caddy local HTTPS contract missing [{$signal}].";
    }
}

$bootstrap = (string) file_get_contents($root.'/bootstrap/app.php');
if (
    ! str_contains($bootstrap, "env('APP_ENV') === 'local'")
    || ! str_contains($bootstrap, "env('SONGCHART_TRUST_DOCKER_PROXY', false)")
    || ! str_contains($bootstrap, "\$middleware->trustProxies(at: '*')")
) {
    $errors[] = 'Trusted proxy support must be explicitly local-only and opt-in for Docker development.';
}

$env = (string) file_get_contents($root.'/.env.docker.example');
foreach ([
    'APP_URL=https://docker.songchart.test:8443',
    'DB_HOST=postgres',
    'REDIS_HOST=redis',
    'SESSION_SECURE_COOKIE=true',
    'SONGCHART_TRUST_DOCKER_PROXY=true',
] as $signal) {
    if (! str_contains($env, $signal)) {
        $errors[] = ".env.docker.example missing [{$signal}].";
    }
}

$songchart = (string) file_get_contents($root.'/songchart');
foreach ([
    'compose.codespaces.yml',
    'CODESPACES:-false',
    'SONGCHART_CODESPACES_APP_URL',
    'GITHUB_CODESPACES_PORT_FORWARDING_DOMAIN:-app.github.dev',
    'dev up -d postgres redis app queue',
    'dev url',
] as $signal) {
    if (! str_contains($songchart, $signal)) {
        $errors[] = "songchart Codespaces workflow missing [{$signal}].";
    }
}

$setup = (string) file_get_contents($root.'/scripts/setup-docker-dev.sh');
foreach ([
    'IS_CODESPACES=false',
    'compose.codespaces.yml',
    'SONGCHART_CODESPACES_APP_URL',
    'if [[ "$IS_CODESPACES" == false ]]',
    'if [[ "$IS_CODESPACES" == true ]]',
] as $signal) {
    if (! str_contains($setup, $signal)) {
        $errors[] = "setup-docker-dev.sh Codespaces workflow missing [{$signal}].";
    }
}

$contract = json_decode((string) file_get_contents($root.'/docs/project/stack/docker-development-contract.json'), true);
if (! is_array($contract)) {
    $errors[] = 'docker-development-contract.json must decode as JSON.';
} else {
    $codespaces = $contract['codespaces'] ?? null;
    if (! is_array($codespaces)
        || ($codespaces['compose_override'] ?? null) !== 'compose.codespaces.yml'
        || ($codespaces['app_host_port'] ?? null) !== 8000
        || ($codespaces['auto_start_services'] ?? null) !== false
        || ($codespaces['url_entrypoint'] ?? null) !== 'songchart dev url') {
        $errors[] = 'Docker development contract must govern the Codespaces adapter, private app port, no-auto-start policy and URL entrypoint.';
    }
}

$gitignore = (string) file_get_contents($root.'/.gitignore');
foreach (['.env.docker', '.certs/*'] as $signal) {
    if (! str_contains($gitignore, $signal)) {
        $errors[] = ".gitignore must protect [{$signal}].";
    }
}

if ($errors !== []) {
    fwrite(STDERR, "Docker local-development verification failed:\n- ".implode("\n- ", $errors).PHP_EOL);

    exit(1);
}

fwrite(STDOUT, 'Docker local-development, trusted HTTPS compatibility and Codespaces development-adapter contract passed.'.PHP_EOL);
