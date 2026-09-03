<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('songchart:about', function (): void {
    $this->info('SongChart Laravel 13 runtime is ready.');
})->purpose('Display SongChart runtime status');

if ((bool) config('songchart.providers.health.schedule_enabled', true)) {
    Schedule::command('providers:health-check')
        ->everyFifteenMinutes()
        ->withoutOverlapping(10)
        ->onOneServer();
}

if ((bool) config('songchart.discovery.schedule_enabled', true)) {
    Schedule::command('discovery:rebuild --all --queue')
        ->everyFifteenMinutes()
        ->withoutOverlapping(10)
        ->onOneServer();
}

$queueMonitorMax = max(1, (int) config('songchart.production.queue_monitor_max', 100));
Schedule::command(
    'queue:monitor redis:critical,redis:discovery-projections,redis:provider-health,redis:provider-imports,redis:provider-normalization,redis:notifications,redis:default --max='.$queueMonitorMax,
)
    ->everyMinute()
    ->withoutOverlapping(2)
    ->onOneServer();

if (class_exists('Laravel\\Horizon\\Horizon')) {
    Schedule::command('horizon:snapshot')
        ->everyFiveMinutes()
        ->onOneServer();
}
