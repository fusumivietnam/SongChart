<?php

declare(strict_types=1);

namespace App\Domain\Discovery\DTO;

use App\Domain\Discovery\Enums\DiscoverySortDirection;
use InvalidArgumentException;

final readonly class DiscoverySort
{
    public function __construct(
        public string $field,
        public DiscoverySortDirection $direction,
    ) {
        if ($field === '') {
            throw new InvalidArgumentException('Discovery sort field must not be empty.');
        }
    }
}
