<?php

declare(strict_types=1);

namespace App\Models\Catalog;

use App\Domain\Catalog\Enums\EntityType;
use App\Domain\Catalog\Enums\RelationshipType;
use App\Domain\Catalog\Enums\VerificationState;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;

final class EntityRelationship extends Model
{
    use HasUlids;

    protected $table = 'entity_relationships';

    protected $fillable = ['subject_type', 'subject_id', 'relationship_type', 'object_type', 'object_id', 'metadata_source_id', 'verification_state', 'metadata'];

    protected function casts(): array
    {
        return [
            'subject_type' => EntityType::class,
            'object_type' => EntityType::class,
            'relationship_type' => RelationshipType::class,
            'verification_state' => VerificationState::class,
            'metadata' => 'array',
        ];
    }
}
