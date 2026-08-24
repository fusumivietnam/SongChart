<?php

declare(strict_types=1);

namespace App\Models\Catalog;

use App\Domain\Catalog\Enums\VerificationState;
use Database\Factories\Catalog\ReleaseFactory;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

final class Release extends Model
{
    /** @use HasFactory<ReleaseFactory> */
    use HasFactory;

    use HasUlids;
    use SoftDeletes;

    protected $table = 'releases';

    protected $fillable = ['release_group_id', 'title', 'slug', 'release_type', 'released_on', 'country_code', 'barcode', 'verification_state'];

    protected function casts(): array
    {
        return [
            'released_on' => 'date',
            'verification_state' => VerificationState::class,
        ];
    }
}
