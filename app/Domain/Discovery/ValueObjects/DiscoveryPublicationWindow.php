<?php

declare(strict_types=1);

namespace App\Domain\Discovery\ValueObjects;

use DateTimeImmutable;
use InvalidArgumentException;

final readonly class DiscoveryPublicationWindow
{
    public function __construct(
        public ?DateTimeImmutable $publishFrom,
        public ?DateTimeImmutable $publishUntil,
        public string $timezone = 'UTC',
    ) {
        if ($publishFrom !== null && $publishUntil !== null && $publishUntil <= $publishFrom) {
            throw new InvalidArgumentException('Discovery publish_until must be later than publish_from.');
        }

        if ($timezone === '') {
            throw new InvalidArgumentException('Discovery publication timezone must not be empty.');
        }
    }

    public function isPublishedAt(DateTimeImmutable $at): bool
    {
        return ($this->publishFrom === null || $this->publishFrom <= $at)
            && ($this->publishUntil === null || $this->publishUntil > $at);
    }
}
