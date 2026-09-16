<?php

declare(strict_types=1);

namespace App\Models;

use App\Domain\Catalog\Enums\EntityType;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class UserSavedEntity extends Model
{
    use HasUlids;

    protected $fillable = [
        'user_id',
        'entity_type',
        'entity_id',
    ];

    protected function casts(): array
    {
        return [
            'entity_type' => EntityType::class,
        ];
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
