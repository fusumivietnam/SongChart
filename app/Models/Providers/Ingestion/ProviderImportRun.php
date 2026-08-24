<?php

declare(strict_types=1);

namespace App\Models\Providers\Ingestion;

use App\Domain\Providers\Ingestion\Enums\ProviderImportRunStatus;
use App\Models\Provider;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class ProviderImportRun extends Model
{
    use HasUlids;

    protected $table = 'provider_import_runs';

    protected $fillable = ['provider_id', 'operation', 'status', 'requested_by_type', 'requested_by_id', 'cursor_start', 'cursor_end', 'configuration_hash', 'configuration', 'statistics', 'error_summary', 'started_at', 'finished_at', 'heartbeat_at', 'resume_after', 'cancellation_requested_at', 'attempts'];

    protected function casts(): array
    {
        return ['status' => ProviderImportRunStatus::class, 'configuration' => 'array', 'statistics' => 'array', 'started_at' => 'datetime', 'finished_at' => 'datetime', 'heartbeat_at' => 'datetime', 'resume_after' => 'datetime', 'cancellation_requested_at' => 'datetime', 'attempts' => 'integer'];
    }

    /** @return BelongsTo<Provider, $this> */
    public function provider(): BelongsTo
    {
        return $this->belongsTo(Provider::class);
    }

    /** @return HasMany<ProviderImportRequest, $this> */
    public function requests(): HasMany
    {
        return $this->hasMany(ProviderImportRequest::class);
    }

    /** @return HasMany<ProviderImportPayload, $this> */
    public function payloads(): HasMany
    {
        return $this->hasMany(ProviderImportPayload::class);
    }

    /** @return HasMany<ProviderImportItem, $this> */
    public function items(): HasMany
    {
        return $this->hasMany(ProviderImportItem::class);
    }

    /** @return HasMany<ProviderImportFailure, $this> */
    public function failures(): HasMany
    {
        return $this->hasMany(ProviderImportFailure::class);
    }

    /** @return HasMany<ProviderImportCheckpoint, $this> */
    public function checkpoints(): HasMany
    {
        return $this->hasMany(ProviderImportCheckpoint::class);
    }
}
