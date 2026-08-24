<?php

declare(strict_types=1);

namespace App\Models\Catalog;

use App\Domain\Catalog\Enums\VerificationState;
use Database\Factories\Catalog\RecordingVersionFactory;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

final class RecordingVersion extends Model
{
    /** @use HasFactory<RecordingVersionFactory> */
    use HasFactory;

    use HasUlids;

    protected $table = 'recording_versions';

    protected $fillable = ['recording_id', 'name', 'slug', 'version_type', 'duration_ms', 'verification_state'];

    protected function casts(): array
    {
        return [
            'verification_state' => VerificationState::class,
        ];
    }
}
