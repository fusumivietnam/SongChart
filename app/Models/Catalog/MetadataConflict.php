<?php

declare(strict_types=1);

namespace App\Models\Catalog;

use App\Domain\Catalog\Enums\EntityType;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;

final class MetadataConflict extends Model
{
    use HasUlids;

    protected $table = 'metadata_conflicts';

    protected $fillable = ['entity_type', 'entity_id', 'field_name', 'left_assertion_id', 'right_assertion_id', 'status', 'resolution_note', 'resolved_at'];

    protected static function booted(): void
    {
        self::saving(function (self $conflict): void {
            $left = (string) $conflict->left_assertion_id;
            $right = (string) $conflict->right_assertion_id;

            if ($left === $right) {
                throw new InvalidArgumentException('A metadata assertion cannot conflict with itself.');
            }

            $pair = [$left, $right];
            sort($pair, SORT_STRING);
            $conflict->setAttribute('normalized_pair_key', implode(':', $pair));
        });
    }

    protected function casts(): array
    {
        return [
            'entity_type' => EntityType::class,
            'resolved_at' => 'datetime',
        ];
    }
}
