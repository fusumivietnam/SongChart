<?php

declare(strict_types=1);

namespace App\Support\Providers\Catalog;

use App\Contracts\Providers\Catalog\ProviderCatalogAdapter;
use App\Domain\Providers\Catalog\DTO\NormalizedProviderEntity;
use App\Domain\Providers\Catalog\DTO\ProviderImportContext;
use App\Domain\Providers\Catalog\DTO\ProviderPage;
use App\Domain\Providers\Catalog\DTO\ProviderPayload;
use App\Domain\Providers\Normalization\DTO\NormalizedEntityDataFactory;

final class NullProviderCatalogAdapter implements ProviderCatalogAdapter
{
    public function __construct(private readonly string $slug = 'null') {}

    public function providerSlug(): string
    {
        return $this->slug;
    }

    public function capabilities(): array
    {
        return [];
    }

    public function fetchPage(ProviderImportContext $context, ?string $cursor = null): ProviderPage
    {
        return new ProviderPage(items: [], complete: true);
    }

    public function normalize(ProviderPayload $payload): NormalizedProviderEntity
    {
        return new NormalizedProviderEntity(
            providerSlug: $payload->providerSlug,
            entityType: $payload->entityType,
            externalId: $payload->externalId,
            data: NormalizedEntityDataFactory::fromProviderAttributes($payload->entityType, $payload->data),
        );
    }
}
