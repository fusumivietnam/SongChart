<?php

declare(strict_types=1);

namespace App\Models;

use App\Domain\Catalog\Enums\EntityType;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class ProviderDestination extends Model
{
    use HasUlids;

    protected $fillable = [
        'provider_id', 'entity_type', 'entity_id', 'provider_resource_id', 'url', 'title',
        'channel_id', 'channel_title', 'duration_ms', 'is_embeddable', 'privacy_status',
        'match_score', 'review_state', 'evidence', 'verified_at', 'last_checked_at',
    ];

    protected function casts(): array
    {
        return [
            'entity_type' => EntityType::class,
            'is_embeddable' => 'boolean',
            'match_score' => 'integer',
            'evidence' => 'array',
            'verified_at' => 'immutable_datetime',
            'last_checked_at' => 'immutable_datetime',
        ];
    }

    /** @return BelongsTo<Provider, $this> */
    public function provider(): BelongsTo
    {
        return $this->belongsTo(Provider::class);
    }
}
