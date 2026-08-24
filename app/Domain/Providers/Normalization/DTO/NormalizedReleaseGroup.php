<?php

declare(strict_types=1);

namespace App\Domain\Providers\Normalization\DTO;

use App\Domain\Catalog\Enums\EntityType;
use App\Domain\Providers\Normalization\Contracts\NormalizedEntityData;
use App\Domain\Providers\Normalization\ValueObjects\ProviderField;

final readonly class NormalizedReleaseGroup implements NormalizedEntityData
{
    public function __construct(
        public ProviderField $title,
        public ProviderField $primaryType,
        public ProviderField $secondaryTypes,
        public ProviderField $firstReleaseDate,
        public ProviderField $disambiguation,
    ) {}

    public function entityType(): EntityType
    {
        return EntityType::ReleaseGroup;
    }

    public function fields(): array
    {
        return [
            'title' => $this->title,
            'primaryType' => $this->primaryType,
            'secondaryTypes' => $this->secondaryTypes,
            'firstReleaseDate' => $this->firstReleaseDate,
            'disambiguation' => $this->disambiguation,
        ];
    }

    public function toArray(): array
    {
        return array_map(static fn (ProviderField $field): array => $field->toArray(), $this->fields());
    }
}
