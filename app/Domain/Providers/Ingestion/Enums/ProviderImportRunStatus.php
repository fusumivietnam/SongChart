<?php

declare(strict_types=1);

namespace App\Domain\Providers\Ingestion\Enums;

use LogicException;

enum ProviderImportRunStatus: string
{
    case Queued = 'queued';
    case Running = 'running';
    case Paused = 'paused';
    case Retrying = 'retrying';
    case Completed = 'completed';
    case CompletedWithErrors = 'completed_with_errors';
    case Failed = 'failed';
    case Cancelled = 'cancelled';

    public function isTerminal(): bool
    {
        return in_array($this, [self::Completed, self::CompletedWithErrors, self::Failed, self::Cancelled], true);
    }

    public function canTransitionTo(self $next): bool
    {
        return match ($this) {
            self::Queued => in_array($next, [self::Running, self::Cancelled, self::Failed], true),
            self::Running => in_array($next, [self::Paused, self::Retrying, self::Completed, self::CompletedWithErrors, self::Failed, self::Cancelled], true),
            self::Paused => in_array($next, [self::Queued, self::Running, self::Cancelled, self::Failed], true),
            self::Retrying => in_array($next, [self::Running, self::Paused, self::Failed, self::Cancelled], true),
            self::Completed, self::CompletedWithErrors, self::Failed, self::Cancelled => false,
        };
    }

    public function assertCanTransitionTo(self $next): void
    {
        if (! $this->canTransitionTo($next)) {
            throw new LogicException("Provider import run cannot transition from [{$this->value}] to [{$next->value}].");
        }
    }
}
