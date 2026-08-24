<?php

declare(strict_types=1);

namespace App\Models\Catalog;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;

final class EnrichmentAttempt extends Model
{
    use HasUlids;

    protected $table = 'enrichment_attempts';

    protected $fillable = [
        'entity_type',
        'entity_id',
        'need_kind',
        'need_key',
        'provider',
        'priority',
        'cost_class',
        'reason',
        'idempotency_key',
        'status',
        'attempt_count',
        'last_error',
        'result_payload',
        'review_reason',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'attempt_count' => 'integer',
            'result_payload' => 'array',
            'completed_at' => 'immutable_datetime',
        ];
    }
}
