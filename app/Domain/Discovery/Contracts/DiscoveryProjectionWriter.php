<?php

declare(strict_types=1);

namespace App\Domain\Discovery\Contracts;

use App\Domain\Discovery\DTO\DiscoveryProjection;

interface DiscoveryProjectionWriter
{
    public function append(DiscoveryProjection $projection): DiscoveryProjection;
}
