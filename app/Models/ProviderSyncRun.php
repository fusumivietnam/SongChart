<?php

declare(strict_types=1);

namespace App\Models;

use App\Domain\Providers\Enums\ProviderSyncOperation;
use App\Domain\Providers\Enums\ProviderSyncStatus;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class ProviderSyncRun extends Model
{
    use HasUlids;

    /** @return BelongsTo<Provider, $this> */
    public function provider(): BelongsTo
    {
        return $this->belongsTo(Provider::class);
    }

    protected $fillable = ['provider_id', 'operation', 'status', 'started_at', 'finished_at', 'processed_count', 'failed_count', 'error_summary'];

    protected function casts(): array
    {
        return [
            'operation' => ProviderSyncOperation::class,
            'status' => ProviderSyncStatus::class,
            'started_at' => 'datetime',
            'finished_at' => 'datetime',
            'processed_count' => 'integer',
            'failed_count' => 'integer',
        ];
    }
}
