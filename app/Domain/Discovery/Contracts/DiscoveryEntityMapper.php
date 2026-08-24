<?php

declare(strict_types=1);

namespace App\Domain\Discovery\Contracts;

use App\Domain\Discovery\DTO\DiscoveryEntitySnapshot;

interface DiscoveryEntityMapper
{
    public function map(object $entity): DiscoveryEntitySnapshot;
}
