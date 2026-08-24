<?php

declare(strict_types=1);

namespace App\Models\Catalog;

use App\Domain\Catalog\Enums\VerificationState;
use Database\Factories\Catalog\CollectionFactory;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

final class Collection extends Model
{
    /** @use HasFactory<CollectionFactory> */
    use HasFactory;

    use HasUlids;
    use SoftDeletes;

    protected $table = 'collections';

    protected $fillable = ['title', 'slug', 'description', 'visibility', 'verification_state'];

    protected function casts(): array
    {
        return [
            'verification_state' => VerificationState::class,
        ];
    }
}
