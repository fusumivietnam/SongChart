<?php

declare(strict_types=1);

namespace App\Contracts\Catalog;

use App\Domain\Catalog\Enrichment\IdentityBridgeSnapshot;
use App\Domain\Catalog\Enums\EntityType;

interface EntityIdentityBridge
{
    public function for(EntityType $type, string $entityId): IdentityBridgeSnapshot;
}
