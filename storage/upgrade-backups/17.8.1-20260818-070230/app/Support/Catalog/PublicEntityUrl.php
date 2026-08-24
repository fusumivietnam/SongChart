<?php

declare(strict_types=1);

namespace App\Support\Catalog;

use App\Domain\Catalog\Enums\EntityType;

final class PublicEntityUrl
{
    public static function routeName(EntityType|string $type): string
    {
        $entityType = is_string($type) ? EntityType::from($type) : $type;

        return match ($entityType) {
            EntityType::Artist => 'artists.show',
            EntityType::ReleaseGroup => 'release-groups.show',
            EntityType::Release => 'releases.show',
            EntityType::Recording => 'recordings.show',
            EntityType::Work => 'works.show',
            EntityType::Version => 'versions.show',
            EntityType::Collection => 'collections.show',
        };
    }

    public static function to(EntityType|string $type, string $slug): string
    {
        return route(self::routeName($type), ['slug' => $slug]);
    }
}
