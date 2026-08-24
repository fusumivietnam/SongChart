<?php

declare(strict_types=1);

namespace App\Services\Analytics;

use App\Contracts\Analytics\ProductAnalytics;

final class NullProductAnalytics implements ProductAnalytics
{
    public function capture(string $event, array $properties = [], ?string $actorId = null): void
    {
        // Deliberate no-op until an approved analytics provider is configured.
    }
}
