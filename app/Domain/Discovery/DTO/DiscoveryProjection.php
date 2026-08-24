<?php

declare(strict_types=1);

namespace App\Domain\Discovery\DTO;

use DateTimeImmutable;

final readonly class DiscoveryProjection
{
    /** @param list<DiscoveryProjectionItem> $items */
    public function __construct(
        public string $channelId,
        public int $channelRevision,
        public int $projectionRevision,
        public int $ruleSchemaVersion,
        public string $sourceVersion,
        public DateTimeImmutable $generatedAt,
        public ?DateTimeImmutable $expiresAt,
        public array $items,
    ) {}
}
