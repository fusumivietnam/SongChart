<?php

declare(strict_types=1);

namespace App\Models\Catalog;

use App\Domain\Catalog\Enums\EntityType;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;

final class CollectionItem extends Model
{
    use HasUlids;

    protected $table = 'collection_items';

    protected $fillable = ['collection_id', 'entity_type', 'entity_id', 'position'];

    protected function casts(): array
    {
        return [
            'entity_type' => EntityType::class,
        ];
    }
}
