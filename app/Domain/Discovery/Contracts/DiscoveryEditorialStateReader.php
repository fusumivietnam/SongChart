<?php

declare(strict_types=1);

namespace App\Domain\Discovery\Contracts;

use App\Domain\Discovery\DTO\DiscoveryEditorialState;

interface DiscoveryEditorialStateReader
{
    public function forChannel(string $channelId): DiscoveryEditorialState;
}
