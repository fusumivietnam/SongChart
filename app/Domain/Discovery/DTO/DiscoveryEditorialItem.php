<?php

declare(strict_types=1);

namespace App\Domain\Discovery\DTO;

use InvalidArgumentException;

final readonly class DiscoveryEditorialItem
{
    public function __construct(
        public string $entityId,
        public ?int $position,
        public bool $pinned,
    ) {
        if ($entityId === '') {
            throw new InvalidArgumentException('Discovery editorial entity id must not be empty.');
        }

        if ($position !== null && ($position < 1 || $position > 500)) {
            throw new InvalidArgumentException('Discovery editorial position must be between 1 and 500.');
        }
    }
}
