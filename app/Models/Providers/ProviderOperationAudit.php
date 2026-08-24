<?php

declare(strict_types=1);

namespace App\Models\Providers;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use LogicException;

final class ProviderOperationAudit extends Model
{
    use HasUlids;

    protected $fillable = ['provider_id', 'provider_import_run_id', 'actor_user_id', 'action', 'idempotency_key', 'before_state', 'after_state', 'rationale', 'occurred_at'];

    protected static function booted(): void
    {
        self::updating(static function (): never {
            throw new LogicException('Provider operation audits are immutable.');
        });
        self::deleting(static function (): never {
            throw new LogicException('Provider operation audits cannot be deleted.');
        });
    }

    protected function casts(): array
    {
        return ['before_state' => 'array', 'after_state' => 'array', 'occurred_at' => 'datetime'];
    }
}
