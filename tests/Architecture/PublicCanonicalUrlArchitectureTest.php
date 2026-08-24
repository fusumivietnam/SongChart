<?php

declare(strict_types=1);

it('keeps public entity URLs plural and does not restore the generic entity route', function (): void {
    $root = dirname(__DIR__, 2);
    $routes = (string) file_get_contents($root.'/routes/web.php');

    expect($routes)
        ->toContain("Route::get('/artists/{slug}'")
        ->toContain("Route::get('/groups/{slug}'")
        ->toContain("Route::get('/releases/{slug}'")
        ->toContain("Route::get('/recordings/{slug}'")
        ->toContain("Route::get('/works/{slug}'")
        ->toContain("Route::get('/collections/{slug}'")
        ->not->toContain("Route::get('/entity/{type}/{slug}'")
        ->not->toContain("Route::get('/artist/{slug}'")
        ->not->toContain("Route::get('/release/{slug}'")
        ->not->toContain("Route::get('/recording/{slug}'");
});
