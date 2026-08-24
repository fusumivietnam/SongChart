<?php

declare(strict_types=1);

namespace App\Domain\Discovery\DTO;

use App\Domain\Discovery\Enums\DiscoverableEntityType;

final readonly class DiscoveryProjectionItem
{
    /** @param array<string, int|float|string|bool|null> $metrics */
    public function __construct(
        public DiscoverableEntityType $entityType,
        public string $entityId,
        public int $rank,
        public string $title,
        public string $slug,
        public ?string $subtitle = null,
        public ?string $image = null,
        public array $metrics = [],
    ) {}
}
