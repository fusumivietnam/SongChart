<?php

declare(strict_types=1);

namespace App\Support\Discovery;

use App\Domain\Discovery\Contracts\DiscoveryEntityMapper;
use App\Domain\Discovery\DTO\DiscoveryEntitySnapshot;
use App\Domain\Discovery\Enums\DiscoverableEntityType;
use App\Models\Catalog\Artist;
use App\Models\Catalog\Collection;
use App\Models\Catalog\Recording;
use App\Models\Catalog\Release;
use BackedEnum;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;

final class CanonicalDiscoveryEntityMapper implements DiscoveryEntityMapper
{
    public function map(object $entity): DiscoveryEntitySnapshot
    {
        return match (true) {
            $entity instanceof Artist => $this->snapshot(DiscoverableEntityType::Artist, $entity, ['name', 'sort_name', 'slug', 'artist_type', 'country_code', 'verification_state', 'created_at', 'updated_at']),
            $entity instanceof Recording => $this->snapshot(DiscoverableEntityType::Recording, $entity, ['title', 'slug', 'duration_ms', 'is_explicit', 'verification_state', 'created_at', 'updated_at']),
            $entity instanceof Release => $this->snapshot(DiscoverableEntityType::Release, $entity, ['title', 'slug', 'release_type', 'released_on', 'country_code', 'verification_state', 'created_at', 'updated_at']),
            $entity instanceof Collection => $this->snapshot(DiscoverableEntityType::Collection, $entity, ['title', 'slug', 'visibility', 'verification_state', 'created_at', 'updated_at']),
            default => throw new InvalidArgumentException('Unsupported canonical entity for discovery mapping.'),
        };
    }

    /** @param list<string> $fields */
    private function snapshot(DiscoverableEntityType $type, Model $entity, array $fields): DiscoveryEntitySnapshot
    {
        $attributes = [];
        foreach ($fields as $field) {
            /** @var mixed $value */
            $value = $entity->getAttribute($field);
            $attributes[$field] = $this->normalize($field, $value);
        }

        return new DiscoveryEntitySnapshot($type, (string) $entity->getKey(), $attributes);
    }

    private function normalize(string $field, mixed $value): string|int|float|bool|null
    {
        if ($value instanceof BackedEnum) {
            return is_int($value->value) ? $value->value : (string) $value->value;
        }

        if ($value instanceof DateTimeInterface) {
            return $value->format($field === 'released_on' ? 'Y-m-d' : DATE_ATOM);
        }

        if ($value === null || is_scalar($value)) {
            return $value;
        }

        throw new InvalidArgumentException('Discovery field mapper encountered a non-scalar canonical value.');
    }
}
