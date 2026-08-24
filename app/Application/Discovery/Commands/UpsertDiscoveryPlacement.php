<?php

declare(strict_types=1);

namespace App\Application\Discovery\Commands;

use App\Domain\Discovery\Enums\DiscoverySurface;
use DateTimeImmutable;

final readonly class UpsertDiscoveryPlacement
{
    public function __construct(
        public string $channelId,
        public DiscoverySurface $surface,
        public string $slot,
        public string $scopeType,
        public string $scopeKey,
        public int $position,
        public ?DateTimeImmutable $startsAt,
        public ?DateTimeImmutable $endsAt,
        public ?string $actorUserId,
    ) {}
}
