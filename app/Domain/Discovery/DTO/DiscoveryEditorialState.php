<?php

declare(strict_types=1);

namespace App\Domain\Discovery\DTO;

final readonly class DiscoveryEditorialState
{
    /**
     * @param  list<DiscoveryEditorialItem>  $items
     * @param  list<string>  $excludedEntityIds
     */
    public function __construct(
        public array $items,
        public array $excludedEntityIds,
    ) {}
}
