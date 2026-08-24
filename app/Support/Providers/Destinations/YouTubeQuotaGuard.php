<?php

declare(strict_types=1);

namespace App\Support\Providers\Destinations;

use Illuminate\Support\Facades\Cache;
use RuntimeException;

final class YouTubeQuotaGuard
{
    public function consume(string $operation): void
    {
        $limit = match ($operation) {
            'search.list' => (int) config('songchart.providers.youtube.quota.search_daily_limit', 100),
            default => (int) config('songchart.providers.youtube.quota.general_daily_units', 10000),
        };

        $key = 'songchart:provider:youtube:quota:'.now('UTC')->format('Y-m-d').':'.$operation;
        $current = (int) Cache::get($key, 0);
        if ($current >= $limit) {
            throw new RuntimeException('YouTube quota guard blocked '.$operation.' for the current UTC day.');
        }

        Cache::put($key, $current + 1, now('UTC')->addDays(2));
    }
}
