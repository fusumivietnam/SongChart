<?php

declare(strict_types=1);

namespace App\Contracts\Analytics;

interface ProductAnalytics
{
    /** @param array<string, scalar|null> $properties */
    public function capture(string $event, array $properties = [], ?string $actorId = null): void;
}
