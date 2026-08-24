<?php

declare(strict_types=1);

namespace App\Domain\Providers\Normalization\DTO;

use App\Domain\Catalog\Enums\EntityType;
use App\Domain\Providers\Normalization\Contracts\NormalizedEntityData;
use App\Domain\Providers\Normalization\ValueObjects\ProviderField;

final readonly class NormalizedWork implements NormalizedEntityData
{
    public function __construct(
        public ProviderField $title,
        public ProviderField $subtitle,
        public ProviderField $languageCode,
        public ProviderField $iswc,
        public ProviderField $type,
        public ProviderField $firstReleaseDate,
    ) {}

    public function entityType(): EntityType
    {
        return EntityType::Work;
    }

    /** @return array<string, ProviderField> */
    public function fields(): array
    {
        return [
            'title' => $this->title,
            'subtitle' => $this->subtitle,
            'languageCode' => $this->languageCode,
            'iswc' => $this->iswc,
            'type' => $this->type,
            'firstReleaseDate' => $this->firstReleaseDate,
        ];
    }

    /** @return array<string, array{presence: string, value?: mixed}> */
    public function toArray(): array
    {
        return array_map(static fn (ProviderField $field): array => $field->toArray(), $this->fields());
    }
}
