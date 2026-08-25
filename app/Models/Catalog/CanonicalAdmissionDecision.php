<?php

declare(strict_types=1);

namespace App\Models\Catalog;

use App\Domain\Catalog\Enums\CanonicalAdmissionStatus;
use App\Domain\Catalog\Enums\EntityType;
use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class CanonicalAdmissionDecision extends Model
{
    use HasUlids;

    protected $table = 'canonical_admission_decisions';

    protected $fillable = [
        'metadata_assertion_id',
        'entity_type',
        'entity_id',
        'field_name',
        'status',
        'canonical_value',
        'decision_reason',
        'reviewer_id',
        'reviewed_at',
        'applied_at',
    ];

    /** @return BelongsTo<MetadataAssertion, $this> */
    public function assertion(): BelongsTo
    {
        return $this->belongsTo(MetadataAssertion::class, 'metadata_assertion_id');
    }

    /** @return BelongsTo<User, $this> */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }

    protected function casts(): array
    {
        return [
            'entity_type' => EntityType::class,
            'status' => CanonicalAdmissionStatus::class,
            'canonical_value' => 'array',
            'reviewed_at' => 'datetime',
            'applied_at' => 'datetime',
        ];
    }
}
