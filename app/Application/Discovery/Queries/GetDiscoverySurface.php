<?php

declare(strict_types=1);

namespace App\Application\Discovery\Queries;

use App\Domain\Discovery\Enums\DiscoverySurface;

final readonly class GetDiscoverySurface
{
    public function __construct(
        public DiscoverySurface $surface,
        public string $scopeType = 'global',
        public string $scopeKey = 'global',
    ) {}
}
