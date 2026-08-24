<?php

declare(strict_types=1);

namespace App\Contracts\Providers;

final readonly class ProviderHealth
{
    public function __construct(
        public bool $healthy,
        public string $summary,
        public ?int $latencyMs = null,
    ) {}
}
