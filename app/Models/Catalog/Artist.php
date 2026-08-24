<?php

declare(strict_types=1);

namespace App\Models\Catalog;

use App\Domain\Catalog\Enums\VerificationState;
use Database\Factories\Catalog\ArtistFactory;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

final class Artist extends Model
{
    /** @use HasFactory<ArtistFactory> */
    use HasFactory;

    use HasUlids;
    use SoftDeletes;

    protected $table = 'artists';

    protected $fillable = ['name', 'sort_name', 'slug', 'artist_type', 'country_code', 'verification_state'];

    protected function casts(): array
    {
        return [
            'verification_state' => VerificationState::class,
        ];
    }
}
