<?php

declare(strict_types=1);

namespace App\Contracts\Providers\Catalog;

interface ProviderCatalogAdapterRegistry
{
    public function for(string $providerSlug): ?ProviderCatalogAdapter;

    /** @return list<string> */
    public function slugs(): array;
}
