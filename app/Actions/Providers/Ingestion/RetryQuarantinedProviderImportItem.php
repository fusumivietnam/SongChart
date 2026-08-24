<?php

declare(strict_types=1);

namespace App\Actions\Providers\Ingestion;

use App\Domain\Providers\Ingestion\Enums\ProviderImportItemStatus;
use App\Jobs\Providers\Ingestion\ProcessProviderImportPayload;
use App\Models\Providers\Ingestion\ProviderImportItem;
use DomainException;

final class RetryQuarantinedProviderImportItem
{
    public function handle(ProviderImportItem $item): void
    {
        if ($item->getAttribute('status') !== ProviderImportItemStatus::Quarantined) {
            throw new DomainException('Only quarantined provider import items may be retried.');
        }

        $item->forceFill([
            'status' => ProviderImportItemStatus::Pending,
            'processed_at' => null,
            'result' => null,
        ])->save();

        ProcessProviderImportPayload::dispatch((string) $item->getKey());
    }
}
