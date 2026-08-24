<?php

declare(strict_types=1);

namespace App\Domain\Providers\Normalization\DTO;

use App\Domain\Catalog\Enums\EntityType;
use App\Domain\Providers\Normalization\Contracts\NormalizedEntityData;
use App\Domain\Providers\Normalization\ValueObjects\ProviderField;

final readonly class NormalizedRelease implements NormalizedEntityData
{
    public function __construct(
        public ProviderField $title,
        public ProviderField $subtitle,
        public ProviderField $barcode,
        public ProviderField $catalogNumber,
        public ProviderField $countryCode,
        public ProviderField $releaseDate,
        public ProviderField $status,
        public ProviderField $primaryType,
        public ProviderField $releaseGroupMbid,
        public ProviderField $packaging,
        public ProviderField $trackCount,
        public ProviderField $coverArtArchiveFrontUrl,
    ) {}

    public function entityType(): EntityType
    {
        return EntityType::Release;
    }

    /** @return array<string, ProviderField> */
    public function fields(): array
    {
        return [
            'title' => $this->title,
            'subtitle' => $this->subtitle,
            'barcode' => $this->barcode,
            'catalogNumber' => $this->catalogNumber,
            'countryCode' => $this->countryCode,
            'releaseDate' => $this->releaseDate,
            'status' => $this->status,
            'primaryType' => $this->primaryType,
            'releaseGroupMbid' => $this->releaseGroupMbid,
            'packaging' => $this->packaging,
            'trackCount' => $this->trackCount,
            'cover_art_archive_front_url' => $this->coverArtArchiveFrontUrl,
        ];
    }

    /** @return array<string, array{presence: string, value?: mixed}> */
    public function toArray(): array
    {
        return array_map(static fn (ProviderField $field): array => $field->toArray(), $this->fields());
    }
}
