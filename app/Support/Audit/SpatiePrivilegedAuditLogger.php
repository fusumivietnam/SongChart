<?php

declare(strict_types=1);

namespace App\Support\Audit;

use App\Domain\Audit\Contracts\PrivilegedAuditLogger;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

final class SpatiePrivilegedAuditLogger implements PrivilegedAuditLogger
{
    public function record(
        string $event,
        string $description,
        ?Model $subject,
        ?User $actor,
        array $before = [],
        array $after = [],
        array $context = [],
        ?string $rationale = null,
    ): void {
        $logger = activity('privileged')
            ->event($event)
            ->withProperties(array_filter([
                'before' => $before === [] ? null : $before,
                'after' => $after === [] ? null : $after,
                'context' => $context === [] ? null : $context,
                'rationale' => $rationale,
            ], static fn (mixed $value): bool => $value !== null));

        if ($subject !== null) {
            $logger->performedOn($subject);
        }

        if ($actor !== null) {
            $logger->causedBy($actor);
        }

        $logger->log($description);
    }
}
