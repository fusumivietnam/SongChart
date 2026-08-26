<?php

declare(strict_types=1);

namespace App\Support\Providers\Normalization;

use App\Domain\Providers\Catalog\DTO\ProviderPayload;
use App\Domain\Providers\Normalization\Contracts\ProviderSpecificMapper;
use LogicException;

final class ProviderSpecificMapperRegistry
{
    /** @param iterable<ProviderSpecificMapper> $mappers */
    public function __construct(private readonly iterable $mappers) {}

    public function mapperFor(ProviderPayload $payload): ProviderSpecificMapper
    {
        foreach ($this->mappers as $mapper) {
            if ($mapper->supports($payload)) {
                return $mapper;
            }
        }

        throw new LogicException(sprintf(
            'No provider-specific mapper is registered for %s:%s.',
            $payload->providerSlug,
            $payload->entityType->value,
        ));
    }
}
