<?php

declare(strict_types=1);

namespace App\Domain\Providers\Normalization\DTO;

use App\Domain\Catalog\Enums\EntityType;
use App\Domain\Providers\Normalization\Contracts\NormalizedEntityData;
use App\Domain\Providers\Normalization\ValueObjects\ProviderField;
use InvalidArgumentException;

final class NormalizedEntityDataFactory
{
    /** @param array<string, mixed> $attributes */
    public static function fromProviderAttributes(EntityType $entityType, array $attributes): NormalizedEntityData
    {
        $field = static fn (string $key): ProviderField => array_key_exists($key, $attributes)
            ? ($attributes[$key] === null ? ProviderField::explicitNull() : ProviderField::provided($attributes[$key]))
            : ProviderField::missing();

        return match ($entityType) {
            EntityType::Artist => new NormalizedArtist($field('name'), $field('sort_name'), $field('disambiguation'), $field('country_code'), $field('begin_date'), $field('end_date'), $field('ended'), $field('type'), $field('aliases'), $field('external_urls')),
            EntityType::Work => new NormalizedWork($field('title'), $field('subtitle'), $field('language_code'), $field('iswc'), $field('type'), $field('first_release_date')),
            EntityType::Recording => new NormalizedRecording($field('title'), $field('subtitle'), $field('duration_ms'), $field('disambiguation'), $field('isrc'), $field('first_release_date'), $field('video')),
            EntityType::ReleaseGroup => new NormalizedReleaseGroup($field('title'), $field('primary_type'), $field('secondary_types'), $field('first_release_date'), $field('disambiguation')),
            EntityType::Release => new NormalizedRelease($field('title'), $field('subtitle'), $field('barcode'), $field('catalog_number'), $field('country_code'), $field('release_date'), $field('status'), $field('primary_type'), $field('release_group_mbid'), $field('packaging'), $field('track_count'), $field('cover_art_archive_front_url')),
            default => throw new InvalidArgumentException("Normalized DTO is not defined for {$entityType->value}."),
        };
    }
}
