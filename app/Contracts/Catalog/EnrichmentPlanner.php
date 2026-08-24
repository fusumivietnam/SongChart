<?php

declare(strict_types=1);

namespace App\Contracts\Catalog;

use App\Domain\Catalog\Enrichment\EnrichmentPlan;
use App\Domain\Catalog\Enums\EntityType;

interface EnrichmentPlanner
{
    public function plan(EntityType $type, string $entityId): EnrichmentPlan;
}
