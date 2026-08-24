<?php

declare(strict_types=1);

namespace App\Application\Discovery\Commands;

use App\Domain\Discovery\Enums\DiscoverableEntityType;
use DateTimeImmutable;

final readonly class PutManualDiscoveryItem
{
    /** @param array<string, scalar|null> $metadata */
    public function __construct(
        public string $channelId,
        public DiscoverableEntityType $entityType,
        public string $entityId,
        public ?int $position,
        public bool $pinned,
        public ?DateTimeImmutable $startsAt,
        public ?DateTimeImmutable $endsAt,
        public array $metadata,
        public ?string $actorUserId,
    ) {}
}
