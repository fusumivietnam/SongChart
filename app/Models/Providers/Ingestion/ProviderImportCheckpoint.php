<?php

declare(strict_types=1);

namespace App\Models\Providers\Ingestion;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class ProviderImportCheckpoint extends Model
{
    use HasUlids;

    protected $table = 'provider_import_checkpoints';

    protected $fillable = ['provider_import_run_id', 'checkpoint_key', 'cursor', 'state', 'committed_at'];

    protected function casts(): array
    {
        return ['state' => 'array', 'committed_at' => 'datetime'];
    }

    /** @return BelongsTo<ProviderImportRun, $this> */
    public function run(): BelongsTo
    {
        return $this->belongsTo(ProviderImportRun::class, 'provider_import_run_id');
    }
}
