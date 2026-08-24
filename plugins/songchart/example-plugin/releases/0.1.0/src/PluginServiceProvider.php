<?php

declare(strict_types=1);

namespace SongChart\ExamplePlugin;

use Illuminate\Support\ServiceProvider;

final class PluginServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        // Register only through published extension points.
    }
}
