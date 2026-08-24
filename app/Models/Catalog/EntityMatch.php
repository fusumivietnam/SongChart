<?php

declare(strict_types=1);

namespace App\Models\Catalog;

use App\Domain\Catalog\Enums\EntityType;
use App\Domain\Catalog\Enums\MatchStatus;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;

final class EntityMatch extends Model
{
    use HasUlids;

    protected $table = 'entity_matches';

    protected $fillable = ['provider_entity_id', 'entity_type', 'entity_id', 'status', 'match_method', 'confidence', 'evidence'];

    protected static function booted(): void
    {
        self::saving(function (self $match): void {
            $statusAttribute = $match->getAttribute('status');
            $status = $statusAttribute instanceof MatchStatus ? $statusAttribute->value : (string) $statusAttribute;
            $match->setAttribute('active_match_key', $status === MatchStatus::Matched->value ? (string) $match->provider_entity_id : null);
        });
    }

    protected function casts(): array
    {
        return [
            'entity_type' => EntityType::class,
            'status' => MatchStatus::class,
            'confidence' => 'decimal:4',
        ];
    }
}
