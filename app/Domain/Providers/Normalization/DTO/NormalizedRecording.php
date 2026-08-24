<?php

declare(strict_types=1);

namespace App\Domain\Providers\Normalization\DTO;

use App\Domain\Catalog\Enums\EntityType;
use App\Domain\Providers\Normalization\Contracts\NormalizedEntityData;
use App\Domain\Providers\Normalization\ValueObjects\ProviderField;

final readonly class NormalizedRecording implements NormalizedEntityData
{
    public function __construct(
        public ProviderField $title,
        public ProviderField $subtitle,
        public ProviderField $durationMs,
        public ProviderField $disambiguation,
        public ProviderField $isrc,
        public ProviderField $firstReleaseDate,
        public ProviderField $video,
    ) {}

    public function entityType(): EntityType
    {
        return EntityType::Recording;
    }

    /** @return array<string, ProviderField> */
    public function fields(): array
    {
        return [
            'title' => $this->title,
            'subtitle' => $this->subtitle,
            'durationMs' => $this->durationMs,
            'disambiguation' => $this->disambiguation,
            'isrc' => $this->isrc,
            'firstReleaseDate' => $this->firstReleaseDate,
            'video' => $this->video,
        ];
    }

    /** @return array<string, array{presence: string, value?: mixed}> */
    public function toArray(): array
    {
        return array_map(static fn (ProviderField $field): array => $field->toArray(), $this->fields());
    }
}
