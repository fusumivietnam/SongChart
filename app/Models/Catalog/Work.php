<?php

declare(strict_types=1);

namespace App\Models\Catalog;

use App\Domain\Catalog\Enums\VerificationState;
use Database\Factories\Catalog\WorkFactory;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

final class Work extends Model
{
    /** @use HasFactory<WorkFactory> */
    use HasFactory;

    use HasUlids;
    use SoftDeletes;

    protected $table = 'works';

    protected $fillable = ['title', 'slug', 'work_type', 'language_code', 'verification_state'];

    protected function casts(): array
    {
        return [
            'verification_state' => VerificationState::class,
        ];
    }
}
