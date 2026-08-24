<?php

declare(strict_types=1);

namespace App\Application\Discovery\Commands;

use App\Domain\Discovery\Enums\DiscoveryChannelStatus;

final readonly class ChangeDiscoveryChannelStatus
{
    public function __construct(
        public string $channelId,
        public DiscoveryChannelStatus $status,
        public ?string $actorUserId,
        public ?string $reason = null,
    ) {}
}
