<?php

declare(strict_types=1);

namespace App\Domain\Providers\Catalog\DTO;

use App\Domain\Catalog\Enums\EntityType;
use App\Domain\Providers\Normalization\Contracts\NormalizedEntityData;
use App\Domain\Providers\Normalization\DTO\NormalizedAvailability;
use App\Domain\Providers\Normalization\DTO\NormalizedClassification;
use App\Domain\Providers\Normalization\DTO\NormalizedDestination;
use App\Domain\Providers\Normalization\DTO\NormalizedIdentifier;
use App\Domain\Providers\Normalization\DTO\NormalizedMediaAsset;
use App\Domain\Providers\Normalization\DTO\NormalizedMetricObservation;
use App\Domain\Providers\Normalization\DTO\NormalizedRelationship;
use InvalidArgumentException;

final readonly class NormalizedProviderEntity
{
    /**
     * @param  list<NormalizedIdentifier>  $identifiers
     * @param  list<NormalizedRelationship>  $relationships
     * @param  list<NormalizedMediaAsset>  $mediaAssets
     * @param  list<NormalizedDestination>  $destinations
     * @param  list<NormalizedAvailability>  $availability
     * @param  list<NormalizedClassification>  $classifications
     * @param  list<NormalizedMetricObservation>  $metrics
     */
    public function __construct(
        public string $providerSlug,
        public EntityType $entityType,
        public string $externalId,
        public NormalizedEntityData $data,
        public array $identifiers = [],
        public array $relationships = [],
        public string $normalizerVersion = '1',
        public array $mediaAssets = [],
        public array $destinations = [],
        public array $availability = [],
        public array $classifications = [],
        public array $metrics = [],
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
            'media_assets' => array_map(static fn (NormalizedMediaAsset $asset): array => $asset->toArray(), $this->mediaAssets),
            'destinations' => array_map(static fn (NormalizedDestination $destination): array => $destination->toArray(), $this->destinations),
            'availability' => array_map(static fn (NormalizedAvailability $item): array => $item->toArray(), $this->availability),
            'classifications' => array_map(static fn (NormalizedClassification $classification): array => $classification->toArray(), $this->classifications),
            'metrics' => array_map(static fn (NormalizedMetricObservation $metric): array => $metric->toArray(), $this->metrics),
        ];
    }
}
