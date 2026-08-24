<?php

declare(strict_types=1);

namespace App\Models\Catalog;

use App\Domain\Catalog\Enums\EntityType;
use App\Domain\Catalog\Enums\VerificationState;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class MetadataAssertion extends Model
{
    use HasUlids;

    protected $table = 'metadata_assertions';

    protected $fillable = ['entity_type', 'entity_id', 'field_name', 'value', 'value_fingerprint', 'metadata_source_id', 'verification_state', 'confidence', 'observed_at', 'expires_at'];

    /** @return BelongsTo<MetadataSource, $this> */
    public function source(): BelongsTo
    {
        return $this->belongsTo(MetadataSource::class, 'metadata_source_id');
    }

    protected function casts(): array
    {
        return [
            'entity_type' => EntityType::class,
            'value' => 'array',
            'verification_state' => VerificationState::class,
            'confidence' => 'decimal:4',
            'observed_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }
}
