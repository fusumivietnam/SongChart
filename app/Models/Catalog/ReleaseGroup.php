<?php

declare(strict_types=1);

namespace App\Models\Catalog;

use App\Domain\Catalog\Enums\VerificationState;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

final class ReleaseGroup extends Model
{
    use HasUlids;
    use SoftDeletes;

    protected $table = 'release_groups';

    protected $fillable = ['title', 'slug', 'primary_type', 'secondary_types', 'first_release_date', 'disambiguation', 'verification_state'];

    protected function casts(): array
    {
        return [
            'secondary_types' => 'array',
            'first_release_date' => 'date',
            'verification_state' => VerificationState::class,
        ];
    }
}
