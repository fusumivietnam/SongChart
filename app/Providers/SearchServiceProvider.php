<?php

declare(strict_types=1);

namespace App\Providers;

use App\Contracts\Search\SearchCatalog;
use App\Support\Search\DemoSearchCatalog;
use App\Support\Search\EloquentSearchCatalog;
use Illuminate\Support\ServiceProvider;

final class SearchServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $useDemoCatalog = $this->app->environment(['local', 'testing'])
            && (bool) config('songchart.search.demo_enabled', false);

        $this->app->singleton(
            SearchCatalog::class,
            $useDemoCatalog ? DemoSearchCatalog::class : EloquentSearchCatalog::class,
        );
    }
}
