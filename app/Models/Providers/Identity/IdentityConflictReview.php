<?php

declare(strict_types=1);

namespace App\Models\Providers\Identity;

use App\Domain\Catalog\Enums\EntityType;
use App\Domain\Providers\Identity\Review\Enums\IdentityConflictReviewStatus;
use App\Models\ProviderEntity;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class IdentityConflictReview extends Model
{
    use HasUlids;

    protected $fillable = [
        'provider_entity_id', 'entity_type', 'status', 'candidate_entity_ids', 'evidence',
        'opened_at', 'resolved_at',
    ];

    protected function casts(): array
    {
        return [
            'entity_type' => EntityType::class,
            'status' => IdentityConflictReviewStatus::class,
            'candidate_entity_ids' => 'array',
            'evidence' => 'array',
            'opened_at' => 'datetime',
            'resolved_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<ProviderEntity, $this> */
    public function providerEntity(): BelongsTo
    {
        return $this->belongsTo(ProviderEntity::class);
    }

    /** @return HasMany<IdentityConflictDecision, $this> */
    public function decisions(): HasMany
    {
        return $this->hasMany(IdentityConflictDecision::class);
    }
}
