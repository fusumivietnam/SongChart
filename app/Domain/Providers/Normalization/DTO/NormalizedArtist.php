<?php

declare(strict_types=1);

namespace App\Domain\Providers\Normalization\DTO;

use App\Domain\Catalog\Enums\EntityType;
use App\Domain\Providers\Normalization\Contracts\NormalizedEntityData;
use App\Domain\Providers\Normalization\ValueObjects\ProviderField;

final readonly class NormalizedArtist implements NormalizedEntityData
{
    public function __construct(
        public ProviderField $name,
        public ProviderField $sortName,
        public ProviderField $disambiguation,
        public ProviderField $countryCode,
        public ProviderField $beginDate,
        public ProviderField $endDate,
        public ProviderField $ended,
        public ?ProviderField $type = null,
        public ?ProviderField $aliases = null,
        public ?ProviderField $externalUrls = null,
    ) {}

    public function entityType(): EntityType
    {
        return EntityType::Artist;
    }

    /** @return array<string, ProviderField> */
    public function fields(): array
    {
        return [
            'name' => $this->name,
            'sortName' => $this->sortName,
            'disambiguation' => $this->disambiguation,
            'countryCode' => $this->countryCode,
            'beginDate' => $this->beginDate,
            'endDate' => $this->endDate,
            'ended' => $this->ended,
            'type' => $this->type ?? ProviderField::missing(),
            'aliases' => $this->aliases ?? ProviderField::missing(),
            'externalUrls' => $this->externalUrls ?? ProviderField::missing(),
        ];
    }

    /** @return array<string, array{presence: string, value?: mixed}> */
    public function toArray(): array
    {
        return array_map(static fn (ProviderField $field): array => $field->toArray(), $this->fields());
    }
}
