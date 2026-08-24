<?php

declare(strict_types=1);

namespace App\Domain\Discovery\Contracts;

use App\Domain\Discovery\DiscoveryChannel;

interface DiscoveryChannelRepository
{
    public function get(string $id): DiscoveryChannel;

    public function save(DiscoveryChannel $channel): void;

    public function existsByKey(string $key): bool;
}
