<?php

declare(strict_types=1);

namespace App\Contracts\Catalog;

use App\Domain\Catalog\Enrichment\EnrichmentExecutionResult;

interface EnrichmentExecutor
{
    /** @param array{id:string,entity_type:string,entity_id:string,provider:string,need_kind:string,need_key:string,reason:string} $attempt */
    public function execute(array $attempt): EnrichmentExecutionResult;
}
