<?php

declare(strict_types=1);

use App\Contracts\Providers\Catalog\ProviderCatalogAdapterRegistry;
use App\Support\Providers\Catalog\InMemoryProviderCatalogAdapterRegistry;

it('binds the provider catalog registry through the application container', function (): void {
    expect(app(ProviderCatalogAdapterRegistry::class))
        ->toBeInstanceOf(InMemoryProviderCatalogAdapterRegistry::class);
});

it('keeps provider catalog adapters behind the tagged registry boundary', function (): void {
    $provider = file_get_contents(app_path('Providers/ProviderServiceProvider.php'));

    expect($provider)
        ->toContain('songchart.provider-catalog-adapters')
        ->not->toContain('Http::');
});
