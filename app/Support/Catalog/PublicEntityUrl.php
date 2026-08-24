<?php

declare(strict_types=1);

namespace App\Support\Catalog;

use App\Domain\Catalog\Enums\EntityType;

final class PublicEntityUrl
{
    /** @var list<string> */
    private const GROUP_ARTIST_TYPES = ['group', 'orchestra', 'choir'];

    public static function routeName(EntityType|string $type, ?string $artistType = null): string
    {
        $entityType = is_string($type) ? EntityType::from($type) : $type;

        return match ($entityType) {
            EntityType::Artist => self::isGroupArtistType($artistType) ? 'groups.show' : 'artists.show',
            EntityType::ReleaseGroup => 'release-groups.show',
            EntityType::Release => 'releases.show',
            EntityType::Recording => 'recordings.show',
            EntityType::Work => 'works.show',
            EntityType::Version => 'versions.show',
            EntityType::Collection => 'collections.show',
        };
    }

    public static function to(EntityType|string $type, string $slug, ?string $artistType = null): string
    {
        return route(self::routeName($type, $artistType), ['slug' => $slug]);
    }

    public static function isGroupArtistType(?string $artistType): bool
    {
        return in_array(strtolower(trim((string) $artistType)), self::GROUP_ARTIST_TYPES, true);
    }
}
