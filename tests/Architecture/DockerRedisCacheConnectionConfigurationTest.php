<?php

declare(strict_types=1);

it('defines the named Redis cache connection used by the cache store', function (): void {
    $root = dirname(__DIR__, 2);
    $database = (string) file_get_contents($root.'/config/database.php');
    $cache = (string) file_get_contents($root.'/config/cache.php');

    expect($cache)
        ->toContain("env('REDIS_CACHE_CONNECTION', 'cache')")
        ->and($database)
        ->toContain("'cache' => [")
        ->toContain("env('REDIS_CACHE_DB', '1')");
});
