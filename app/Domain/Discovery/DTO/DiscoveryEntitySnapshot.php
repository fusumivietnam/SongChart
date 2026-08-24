<?php

declare(strict_types=1);

namespace App\Domain\Discovery\DTO;

use App\Domain\Discovery\Enums\DiscoverableEntityType;
use InvalidArgumentException;

final readonly class DiscoveryEntitySnapshot
{
    /** @param array<string, scalar|null> $attributes */
    public function __construct(
        public DiscoverableEntityType $entityType,
        public string $id,
        public array $attributes,
    ) {
        if ($id === '') {
            throw new InvalidArgumentException('Discovery entity snapshot id must not be empty.');
        }
    }

    public function has(string $field): bool
    {
        return $field === 'id' || array_key_exists($field, $this->attributes);
    }

    public function value(string $field): string|int|float|bool|null
    {
        if ($field === 'id') {
            return $this->id;
        }

        return $this->attributes[$field] ?? null;
    }
}
