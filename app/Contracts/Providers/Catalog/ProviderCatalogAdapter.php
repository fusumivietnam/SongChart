<?php

declare(strict_types=1);

namespace App\Contracts\Providers\Catalog;

use App\Domain\Providers\Catalog\DTO\NormalizedProviderEntity;
use App\Domain\Providers\Catalog\DTO\ProviderImportContext;
use App\Domain\Providers\Catalog\DTO\ProviderPage;
use App\Domain\Providers\Catalog\DTO\ProviderPayload;
use App\Domain\Providers\Catalog\Enums\ProviderCatalogCapability;

interface ProviderCatalogAdapter
{
    public function providerSlug(): string;

    /** @return list<ProviderCatalogCapability> */
    public function capabilities(): array;

    public function fetchPage(ProviderImportContext $context, ?string $cursor = null): ProviderPage;

    public function normalize(ProviderPayload $payload): NormalizedProviderEntity;
}
