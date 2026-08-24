<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class ExtensionOperation extends Model
{
    use HasUlids;

    protected $fillable = ['extension_id', 'type', 'status', 'actor_id', 'source', 'checksum_sha256', 'details', 'started_at', 'finished_at', 'error_summary'];

    protected function casts(): array
    {
        return ['details' => 'array', 'started_at' => 'datetime', 'finished_at' => 'datetime'];
    }

    /** @return BelongsTo<Extension, $this> */
    public function extension(): BelongsTo
    {
        return $this->belongsTo(Extension::class);
    }
}
