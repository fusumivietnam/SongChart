<?php

declare(strict_types=1);

namespace App\Domain\Discovery\Contracts;

use App\Domain\Discovery\DTO\DiscoveryEntitySnapshot;
use App\Domain\Discovery\Enums\DiscoverableEntityType;

interface DiscoveryEntitySource
{
    /** @return iterable<list<DiscoveryEntitySnapshot>> */
    public function batches(DiscoverableEntityType $entityType, int $batchSize): iterable;

    /**
     * @param  list<string>  $ids
     * @return list<DiscoveryEntitySnapshot>
     */
    public function find(DiscoverableEntityType $entityType, array $ids): array;

    public function version(DiscoverableEntityType $entityType): string;
}
