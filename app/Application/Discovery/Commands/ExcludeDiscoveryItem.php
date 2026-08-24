<?php

declare(strict_types=1);

namespace App\Application\Discovery\Commands;

use App\Domain\Discovery\Enums\DiscoverableEntityType;
use DateTimeImmutable;

final readonly class ExcludeDiscoveryItem
{
    public function __construct(
        public string $channelId,
        public DiscoverableEntityType $entityType,
        public string $entityId,
        public ?string $reason,
        public ?DateTimeImmutable $expiresAt,
        public ?string $actorUserId,
    ) {}
}
