<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$errors = [];

$required = [
    'compose.dev.yml',
    'compose.codespaces.yml',
    '.env.docker.example',
    'docker/dev/Caddyfile',
    'scripts/setup-docker-dev.sh',
    'scripts/recover-docker-dev-runtime.sh',
    'songchart',
    'docs/project/stack/docker-development-contract.json',
    'docs/project/engineering/development-database-contract.json',
    'app/Support/Development/DevelopmentDatabaseAuthority.php',
    'app/Console/Commands/DevelopmentDatabaseStatusCommand.php',
];

foreach ($required as $relative) {
    if (! is_file($root.'/'.$relative)) {
        $errors[] = "Missing Docker local-development file [{$relative}].";
    }
}

foreach ([
    'songchart.bat',
    'docker-dev-setup.bat',
    'docker-dev-up.bat',
    'docker-dev-down.bat',
    'scripts/setup-docker-dev.ps1',
    'scripts/docker-dev-up.ps1',
    'scripts/docker-dev-down.ps1',
    'scripts/setup-laragon.bat',
] as $retired) {
    if (is_file($root.'/'.$retired)) {
        $errors[] = "Retired host-specific development file must be removed [{$retired}].";
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
    'command: ["php", "artisan", "horizon"]',
] as $signal) {
    if (! str_contains($compose, $signal)) {
        $errors[] = "compose.dev.yml missing [{$signal}].";
    }
}

foreach ([
    'DB_HOST: postgres',
    'DB_DATABASE: songchart_docker',
    'DB_USERNAME: songchart_docker',
    'DB_PASSWORD: songchart_docker_only',
] as $forbiddenOverride) {
    if (str_contains($compose, $forbiddenOverride)) {
        $errors[] = "compose.dev.yml must not override development database authority with [{$forbiddenOverride}].";
    }
}

if (str_contains($compose, 'queue:work')) {
    $errors[] = 'Docker local queue runtime must use Horizon and must not retain native queue:work supervision.';
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
    'SONGCHART_DEV_DATABASE_MODE=local',
    'SONGCHART_DEV_DATABASE_EXPECTED_NAME=songchart_docker',
    'DB_URL=',
    'DB_HOST=postgres',
    'DB_SSLMODE=prefer',
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
    'SONGCHART_DEV_DATABASE_MODE',
    'Using durable remote PostgreSQL authority; local postgres service will not be started.',
    '.songchart-db-backups',
    'postgres:18.4-bookworm',
    'development:database-status',
    'dev db status|backup|restore',
    'dev url',
] as $signal) {
    if (! str_contains($songchart, $signal)) {
        $errors[] = "songchart Codespaces/database workflow missing [{$signal}].";
    }
}
if (str_contains($songchart, 'BACKUP_DIR="$ROOT/.songchart-backups"')) {
    $errors[] = '.songchart-backups is source/file overwrite recovery authority and must not be reused for development database dumps.';
}

$setup = (string) file_get_contents($root.'/scripts/setup-docker-dev.sh');
foreach ([
    'IS_CODESPACES=false',
    'compose.codespaces.yml',
    'SONGCHART_CODESPACES_APP_URL',
    'if [[ "$IS_CODESPACES" == false ]]',
    'if [[ "$IS_CODESPACES" == true ]]',
    'recover-docker-dev-runtime.sh',
    'DEV_DB_MODE=',
    'Remote development database mode requires DB_URL',
    'local postgres service remains stopped',
    'development:database-status --json',
] as $signal) {
    if (! str_contains($setup, $signal)) {
        $errors[] = "setup-docker-dev.sh Codespaces/recovery/database workflow missing [{$signal}].";
    }
}

$recovery = (string) file_get_contents($root.'/scripts/recover-docker-dev-runtime.sh');
foreach ([
    'com.docker.compose.project=',
    'docker rm -f',
    'named volumes are preserved',
] as $signal) {
    if (! str_contains($recovery, $signal)) {
        $errors[] = "recover-docker-dev-runtime.sh missing [{$signal}].";
    }
}
foreach (['volume rm', 'volume prune', 'down -v', 'system prune --volumes'] as $unsafe) {
    if (str_contains($recovery, $unsafe)) {
        $errors[] = "recover-docker-dev-runtime.sh must not contain destructive volume operation [{$unsafe}].";
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
    $recoveryContract = $contract['development']['runtime_recovery'] ?? null;
    if (! is_array($recoveryContract)
        || ($contract['development']['runtime_recovery_entrypoint'] ?? null) !== 'bash scripts/recover-docker-dev-runtime.sh'
        || ($recoveryContract['named_volumes_must_be_preserved'] ?? null) !== true
        || ($recoveryContract['container_objects_are_disposable'] ?? null) !== true) {
        $errors[] = 'Docker development contract must govern non-destructive stale-container recovery and preserve named volumes.';
    }
    if (($contract['compatibility']['native_windows_cli']['status'] ?? null) !== 'retired') {
        $errors[] = 'Docker development contract must mark native Windows CLI as retired.';
    }
}

$databaseContract = json_decode((string) file_get_contents($root.'/docs/project/engineering/development-database-contract.json'), true);
if (! is_array($databaseContract)
    || ($databaseContract['modes']['remote']['allows_local_host_fallback'] ?? null) !== false
    || ($databaseContract['modes']['local']['explicit_fallback_only'] ?? null) !== true
    || ($databaseContract['backup']['database_backup_directory'] ?? null) !== '.songchart-db-backups') {
    $errors[] = 'Development database contract must govern remote fail-closed behavior, explicit local fallback and separate database backup storage.';
}

$gitignore = (string) file_get_contents($root.'/.gitignore');
foreach (['.env.docker', '.certs/*', '/.songchart-db-backups/*'] as $signal) {
    if (! str_contains($gitignore, $signal)) {
        $errors[] = ".gitignore must protect [{$signal}].";
    }
}

if ($errors !== []) {
    fwrite(STDERR, "Docker local-development verification failed:\n- ".implode("\n- ", $errors).PHP_EOL);
    exit(1);
}

fwrite(STDOUT, 'Docker local-development and Codespaces adapter contract passed.'.PHP_EOL);
