<?php

declare(strict_types=1);

namespace App\Domain\Providers\Catalog\DTO;

use App\Domain\Providers\Catalog\ValueObjects\ProviderRateLimitState;

final readonly class ProviderPage
{
    /** @param list<ProviderPayload> $items */
    public function __construct(
        public array $items,
        public ?string $nextCursor = null,
        public ?ProviderRateLimitState $rateLimit = null,
        public bool $complete = false,
    ) {}
}
