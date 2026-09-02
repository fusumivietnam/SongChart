<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$errors = [];

$read = static fn (string $path): string => (string) @file_get_contents($root.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $path));

$composer = json_decode($read('composer.json'), true);
if (! is_array($composer) || ($composer['require']['php'] ?? null) !== '^8.5') {
    $errors[] = 'Composer PHP baseline must be ^8.5.';
}
if (isset($composer['require']['laravel/horizon']) || isset($composer['require-dev']['laravel/horizon'])) {
    $errors[] = 'Horizon must remain optional for the first-release production runtime.';
}
if (! isset($composer['suggest']['laravel/horizon'])) {
    $errors[] = 'Composer must advertise the optional Horizon profile.';
}

$queue = $read('config/queue.php');
if (! str_contains($queue, "env('QUEUE_CONNECTION', 'redis')") || ! str_contains($queue, "env('REDIS_QUEUE_RETRY_AFTER', 180)")) {
    $errors[] = 'Redis must be the queue baseline with a 180-second retry_after default.';
}

$horizon = $read('config/horizon.php');
foreach (['critical', 'discovery-projections', 'provider-health', 'provider-imports', 'provider-normalization', 'notifications', 'default'] as $name) {
    if (! str_contains($horizon, "'{$name}'")) {
        $errors[] = "Horizon profile is missing queue [{$name}].";
    }
}

$appProvider = $read('app/Providers/AuthorizationServiceProvider.php');
$capabilities = $read('app/Enums/Capability.php');
if (! str_contains($appProvider, 'foreach (Capability::cases() as $capability)')
    || ! str_contains($capabilities, "case ViewHorizon = 'viewHorizon';")
) {
    $errors[] = 'Optional Horizon dashboard authorization must remain registered from the shared Capability authority.';
}
if (is_file($root.DIRECTORY_SEPARATOR.'app'.DIRECTORY_SEPARATOR.'Providers'.DIRECTORY_SEPARATOR.'HorizonServiceProvider.php')) {
    $errors[] = 'Do not ship an application Horizon provider while Horizon remains optional.';
}

$console = $read('routes/console.php');
foreach ([
    "Schedule::command('horizon:snapshot')",
    "queue:monitor redis:critical,redis:discovery-projections,redis:provider-health,redis:provider-imports,redis:provider-normalization,redis:notifications,redis:default --max=",
    '->everyMinute()',
    '->withoutOverlapping(2)',
    '->onOneServer()',
] as $signal) {
    if (! str_contains($console, $signal)) {
        $errors[] = "Queue/scheduler operational schedule is missing [{$signal}].";
    }
}

$worker = $read('scripts/production/queue-worker.sh');
foreach ([
    'exec php artisan queue:work redis',
    '--queue="$QUEUES"',
    '--tries="$TRIES"',
    '--timeout="$TIMEOUT"',
    '--backoff="$BACKOFF"',
    '--max-time="$MAX_TIME"',
    '--max-jobs="$MAX_JOBS"',
    '--memory="$MEMORY"',
] as $signal) {
    if (! str_contains($worker, $signal)) {
        $errors[] = "Production queue worker entrypoint is missing [{$signal}].";
    }
}
if (str_contains($worker, 'while ') || str_contains($worker, 'until ')) {
    $errors[] = 'Production queue wrapper must delegate directly to Laravel and must not implement a custom worker loop.';
}

$scheduler = $read('scripts/production/scheduler.sh');
if (! str_contains($scheduler, 'exec php artisan schedule:work')) {
    $errors[] = 'Production scheduler entrypoint must delegate directly to Laravel schedule:work.';
}

$runtime = json_decode($read('docs/project/stack/runtime-environments.json'), true);
$production = is_array($runtime) ? ($runtime['profiles']['production'] ?? []) : [];
foreach ([
    'app_env' => 'production',
    'db_connection' => 'pgsql',
    'queue_connection' => 'redis',
    'cache_store' => 'redis',
    'worker_entrypoint' => 'sh scripts/production/queue-worker.sh',
    'scheduler_entrypoint' => 'sh scripts/production/scheduler.sh',
] as $key => $value) {
    if (($production[$key] ?? null) !== $value) {
        $errors[] = "Production runtime profile must declare {$key}={$value}.";
    }
}
if (($production['scheduler_instances'] ?? null) !== 1 || ($production['process_manager_restart_required'] ?? null) !== true) {
    $errors[] = 'Production runtime must own one scheduler lifecycle and require process-manager restart semantics.';
}

$env = $read('.env.production.example');
foreach ([
    'REDIS_QUEUE_RETRY_AFTER=180',
    'SONGCHART_QUEUE_TIMEOUT_SECONDS=150',
    'SONGCHART_QUEUE_MAX_TIME_SECONDS=3600',
    'SONGCHART_QUEUE_MAX_JOBS=1000',
    'SONGCHART_QUEUE_MEMORY_MB=256',
    'SONGCHART_QUEUE_MONITOR_MAX=100',
] as $signal) {
    if (! str_contains($env, $signal)) {
        $errors[] = "Production environment queue lifecycle contract is missing [{$signal}].";
    }
}

$discovery = $read('app/Jobs/Discovery/BuildDiscoveryProjection.php');
foreach (['public int $tries = 3', 'public int $timeout = 120', 'function backoff()', 'function tags()', 'discovery-projections'] as $signal) {
    if (! str_contains($discovery, $signal)) {
        $errors[] = "Discovery projection job is missing operational signal [{$signal}].";
    }
}

foreach ([
    'app/Jobs/Providers/Ingestion/FetchProviderImportPage.php' => 'provider-imports',
    'app/Jobs/Providers/Ingestion/FinalizeProviderImport.php' => 'provider-imports',
    'app/Jobs/Providers/Ingestion/ProcessProviderImportPayload.php' => 'provider-normalization',
] as $file => $queueName) {
    if (! str_contains($read($file), $queueName)) {
        $errors[] = "{$file} must route to {$queueName}.";
    }
}

$docs = $read('docs/operations/queue-infrastructure.md');
foreach (['queue:restart', 'schedule:work', 'queue:monitor', 'failed_jobs', 'process manager', 'Do not introduce custom worker loops'] as $signal) {
    if (! str_contains($docs, $signal)) {
        $errors[] = "Queue operations documentation is missing production lifecycle signal [{$signal}].";
    }
}

if ($errors !== []) {
    fwrite(STDERR, "Queue infrastructure verification failed:\n- ".implode("\n- ", array_unique($errors))."\n");
    exit(1);
}

fwrite(STDOUT, "Queue infrastructure verification passed.\n");
