<?php

declare(strict_types=1);

use App\Jobs\Providers\CheckProviderHealth;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;

it('keeps provider network health work behind a unique queued job', function (): void {
    expect(is_subclass_of(CheckProviderHealth::class, ShouldQueue::class))->toBeTrue()
        ->and(is_subclass_of(CheckProviderHealth::class, ShouldBeUnique::class))->toBeTrue();
});

it('keeps provider health dispatch on the Laravel scheduler', function (): void {
    $consoleRoutes = (string) file_get_contents(base_path('routes/console.php'));

    expect($consoleRoutes)
        ->toContain("Schedule::command('providers:health-check')")
        ->toContain('withoutOverlapping')
        ->toContain('onOneServer');
});
