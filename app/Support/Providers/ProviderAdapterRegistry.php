<?php

declare(strict_types=1);

namespace App\Support\Providers;

use App\Contracts\Providers\ProviderAdapter;
use InvalidArgumentException;

final class ProviderAdapterRegistry
{
    /** @var array<string, ProviderAdapter> */
    private array $adapters = [];

    /** @param iterable<ProviderAdapter> $adapters */
    public function __construct(iterable $adapters = [])
    {
        foreach ($adapters as $adapter) {
            $slug = $adapter->providerSlug();

            if (isset($this->adapters[$slug])) {
                throw new InvalidArgumentException("Duplicate provider adapter registered for [{$slug}].");
            }

            $this->adapters[$slug] = $adapter;
        }
    }

    public function for(string $providerSlug): ?ProviderAdapter
    {
        return $this->adapters[$providerSlug] ?? null;
    }

    /** @return list<string> */
    public function slugs(): array
    {
        return array_keys($this->adapters);
    }
}
