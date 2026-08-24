<?php

declare(strict_types=1);

namespace App\Domain\Providers\Destinations\DTO;

final readonly class VideoDestinationCandidate
{
    /** @param array<string,mixed> $evidence */
    public function __construct(
        public string $resourceId,
        public string $url,
        public string $title,
        public string $channelId,
        public string $channelTitle,
        public ?int $durationMs,
        public bool $embeddable,
        public string $privacyStatus,
        public int $score,
        public string $decision,
        public array $evidence = [],
    ) {}
}
