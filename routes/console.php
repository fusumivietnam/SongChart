<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('songchart:about', function (): void {
    $this->info('SongChartWeb Laravel 13 starter is ready.');
})->purpose('Display SongChart starter status');

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

if (class_exists('Laravel\\Horizon\\Horizon')) {
    Schedule::command('horizon:snapshot')
        ->everyFiveMinutes()
        ->onOneServer();
}
