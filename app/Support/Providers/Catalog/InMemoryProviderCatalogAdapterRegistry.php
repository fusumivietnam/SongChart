<?php

declare(strict_types=1);

namespace App\Support\Providers\Catalog;

use App\Contracts\Providers\Catalog\ProviderCatalogAdapter;
use App\Contracts\Providers\Catalog\ProviderCatalogAdapterRegistry;
use InvalidArgumentException;

final class InMemoryProviderCatalogAdapterRegistry implements ProviderCatalogAdapterRegistry
{
    /** @var array<string, ProviderCatalogAdapter> */
    private array $adapters = [];

    /** @param iterable<ProviderCatalogAdapter> $adapters */
    public function __construct(iterable $adapters = [])
    {
        foreach ($adapters as $adapter) {
            $slug = $adapter->providerSlug();

            if ($slug === '' || isset($this->adapters[$slug])) {
                throw new InvalidArgumentException("Invalid or duplicate provider catalog adapter [{$slug}].");
            }

            $this->adapters[$slug] = $adapter;
        }
    }

    public function for(string $providerSlug): ?ProviderCatalogAdapter
    {
        return $this->adapters[$providerSlug] ?? null;
    }

    public function slugs(): array
    {
        return array_keys($this->adapters);
    }
}
