<?php

declare(strict_types=1);

namespace App\Domain\Audit\Contracts;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

interface PrivilegedAuditLogger
{
    /**
     * @param  array<string, mixed>  $before
     * @param  array<string, mixed>  $after
     * @param  array<string, mixed>  $context
     */
    public function record(
        string $event,
        string $description,
        ?Model $subject,
        ?User $actor,
        array $before = [],
        array $after = [],
        array $context = [],
        ?string $rationale = null,
    ): void;
}
