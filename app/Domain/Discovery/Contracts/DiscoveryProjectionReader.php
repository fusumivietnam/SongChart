<?php

declare(strict_types=1);

namespace App\Domain\Discovery\Contracts;

use App\Domain\Discovery\DTO\DiscoveryProjection;

interface DiscoveryProjectionReader
{
    public function latestForChannel(string $channelId): ?DiscoveryProjection;
}
