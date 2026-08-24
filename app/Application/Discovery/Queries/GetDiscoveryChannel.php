<?php

declare(strict_types=1);

namespace App\Application\Discovery\Queries;

final readonly class GetDiscoveryChannel
{
    public function __construct(public string $channelId) {}
}
