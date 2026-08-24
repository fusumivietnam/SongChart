<?php

declare(strict_types=1);

namespace App\Domain\Discovery\DTO;

use App\Domain\Discovery\Enums\DiscoveryDomainEventType;
use DateTimeImmutable;

final readonly class DiscoveryDomainEvent
{
    /** @param array<string, scalar|null> $context */
    public function __construct(
        public DiscoveryDomainEventType $type,
        public string $channelId,
        public DateTimeImmutable $occurredAt,
        public array $context = [],
    ) {}
}
