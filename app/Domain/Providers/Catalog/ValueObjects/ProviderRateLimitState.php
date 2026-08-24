<?php

declare(strict_types=1);

namespace App\Domain\Providers\Catalog\ValueObjects;

use DateTimeImmutable;
use InvalidArgumentException;

final readonly class ProviderRateLimitState
{
    public function __construct(
        public ?int $limit = null,
        public ?int $remaining = null,
        public ?DateTimeImmutable $resetsAt = null,
        public ?int $retryAfterSeconds = null,
    ) {
        if ($limit !== null && $limit < 0) {
            throw new InvalidArgumentException('Rate limit must be zero or greater.');
        }

        if ($remaining !== null && $remaining < 0) {
            throw new InvalidArgumentException('Remaining requests must be zero or greater.');
        }

        if ($retryAfterSeconds !== null && $retryAfterSeconds < 0) {
            throw new InvalidArgumentException('Retry-after must be zero or greater.');
        }
    }
}
