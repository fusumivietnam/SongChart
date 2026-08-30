<?php

declare(strict_types=1);

namespace App\Contracts\Providers\Destinations;

use App\Domain\Providers\Destinations\DTO\VideoDestinationCandidate;
use App\Models\Catalog\Recording;

interface VideoDestinationDiscovery
{
    /** @return list<VideoDestinationCandidate> */
    public function candidates(Recording $recording, int $limit = 5): array;

    public function verify(string $resourceId, Recording $recording): VideoDestinationCandidate;

    public function inspect(string $resourceId, Recording $recording): ?VideoDestinationCandidate;
}
