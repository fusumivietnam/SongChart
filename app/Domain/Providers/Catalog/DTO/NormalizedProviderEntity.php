<?php

declare(strict_types=1);

namespace App\Domain\Providers\Catalog\DTO;

use App\Domain\Catalog\Enums\EntityType;
use App\Domain\Providers\Normalization\Contracts\NormalizedEntityData;
use App\Domain\Providers\Normalization\DTO\NormalizedIdentifier;
use App\Domain\Providers\Normalization\DTO\NormalizedRelationship;
use InvalidArgumentException;

final readonly class NormalizedProviderEntity
{
    /**
     * @param  list<NormalizedIdentifier>  $identifiers
     * @param  list<NormalizedRelationship>  $relationships
     */
    public function __construct(
        public string $providerSlug,
        public EntityType $entityType,
        public string $externalId,
        public NormalizedEntityData $data,
        public array $identifiers = [],
        public array $relationships = [],
        public string $normalizerVersion = '1',
    ) {
        if ($data->entityType() !== $entityType) {
            throw new InvalidArgumentException('Normalized entity data type must match the envelope entity type.');
        }
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'provider_slug' => $this->providerSlug,
            'entity_type' => $this->entityType->value,
            'external_id' => $this->externalId,
            'fields' => $this->data->toArray(),
            'identifiers' => array_map(static fn (NormalizedIdentifier $identifier): array => $identifier->toArray(), $this->identifiers),
            'relationships' => array_map(static fn (NormalizedRelationship $relationship): array => $relationship->toArray(), $this->relationships),
            'normalizer_version' => $this->normalizerVersion,
        ];
    }
}
