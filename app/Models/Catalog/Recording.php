<?php

declare(strict_types=1);

namespace App\Models\Catalog;

use App\Domain\Catalog\Enums\VerificationState;
use Database\Factories\Catalog\RecordingFactory;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

final class Recording extends Model
{
    /** @use HasFactory<RecordingFactory> */
    use HasFactory;

    use HasUlids;
    use SoftDeletes;

    protected $table = 'recordings';

    protected $fillable = ['work_id', 'title', 'slug', 'duration_ms', 'is_explicit', 'verification_state'];

    /** @return BelongsToMany<Artist, $this> */
    public function artists(): BelongsToMany
    {
        return $this->belongsToMany(Artist::class, 'artist_recording')
            ->withPivot(['credit_role', 'position', 'credited_name', 'join_phrase'])
            ->orderByPivot('position');
    }

    protected function casts(): array
    {
        return [
            'is_explicit' => 'boolean',
            'verification_state' => VerificationState::class,
        ];
    }
}
