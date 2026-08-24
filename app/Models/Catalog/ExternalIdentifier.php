<?php

declare(strict_types=1);

namespace App\Models\Catalog;

use App\Domain\Catalog\Enums\EntityType;
use App\Domain\Catalog\Enums\VerificationState;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;

final class ExternalIdentifier extends Model
{
    use HasUlids;

    protected $table = 'external_identifiers';

    protected $fillable = ['entity_type', 'entity_id', 'namespace', 'value', 'metadata_source_id', 'is_primary', 'verification_state'];

    protected function casts(): array
    {
        return [
            'entity_type' => EntityType::class,
            'is_primary' => 'boolean',
            'verification_state' => VerificationState::class,
        ];
    }
}
