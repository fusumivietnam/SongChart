<?php

declare(strict_types=1);

namespace App\Application\Discovery\Queries;

use App\Domain\Discovery\Enums\DiscoverableEntityType;
use App\Domain\Discovery\Enums\DiscoveryChannelStatus;

final readonly class ListDiscoveryChannels
{
    public function __construct(
        public ?DiscoveryChannelStatus $status = null,
        public ?DiscoverableEntityType $entityType = null,
        public int $perPage = 25,
    ) {}
}
