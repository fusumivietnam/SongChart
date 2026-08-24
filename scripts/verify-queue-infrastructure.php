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
    $errors[] = 'Horizon must not be a mandatory dependency while native Windows/Laragon remains a supported dev target.';
}
if (! isset($composer['suggest']['laravel/horizon'])) {
    $errors[] = 'Composer must advertise the optional Linux/WSL Horizon profile.';
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
    $errors[] = 'Horizon dashboard authorization must be registered from the shared Capability authority.';
}
if (is_file($root.DIRECTORY_SEPARATOR.'app'.DIRECTORY_SEPARATOR.'Providers'.DIRECTORY_SEPARATOR.'HorizonServiceProvider.php')) {
    $errors[] = 'Do not ship an application Horizon provider while Horizon is optional; it would create a missing-parent static-analysis dependency on Windows.';
}

$console = $read('routes/console.php');
foreach (["Schedule::command('horizon:snapshot')", '->everyFiveMinutes()', '->onOneServer()'] as $signal) {
    if (! str_contains($console, $signal)) {
        $errors[] = "Horizon metrics snapshot schedule is missing [{$signal}].";
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
if (! str_contains($docs, 'ext-pcntl') || ! str_contains($docs, 'ext-posix') || ! str_contains($docs, 'Do not use `--ignore-platform-reqs`')) {
    $errors[] = 'Queue operations documentation must preserve the Horizon Windows compatibility warning.';
}

if ($errors !== []) {
    fwrite(STDERR, "Queue infrastructure verification failed:\n- ".implode("\n- ", array_unique($errors))."\n");
    exit(1);
}

fwrite(STDOUT, "Queue infrastructure verification passed.\n");
