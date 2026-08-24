<?php

declare(strict_types=1);

namespace App\Domain\Providers\Enums;

enum ProviderSyncStatus: string
{
    case Queued = 'queued';
    case Running = 'running';
    case Retrying = 'retrying';
    case Succeeded = 'succeeded';
    case Failed = 'failed';
    case Skipped = 'skipped';

    public function isTerminal(): bool
    {
        return in_array($this, [self::Succeeded, self::Failed, self::Skipped], true);
    }

    /** @return list<string> */
    public static function pendingValues(): array
    {
        return [self::Queued->value, self::Running->value, self::Retrying->value];
    }
}
