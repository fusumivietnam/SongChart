<?php

declare(strict_types=1);

namespace App\Domain\Providers\Ingestion\Enums;

enum ProviderImportItemStatus: string
{
    case Pending = 'pending';
    case Normalized = 'normalized';
    case Matched = 'matched';
    case Applied = 'applied';
    case Skipped = 'skipped';
    case Quarantined = 'quarantined';
    case Failed = 'failed';
}
