<?php

declare(strict_types=1);

namespace App\Models\Providers\Ingestion;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class ProviderImportRequest extends Model
{
    use HasUlids;

    protected $table = 'provider_import_requests';

    protected $fillable = ['provider_import_run_id', 'sequence', 'method', 'endpoint', 'query', 'request_headers', 'response_status', 'response_headers', 'duration_ms', 'attempt', 'requested_at', 'responded_at'];

    protected function casts(): array
    {
        return ['query' => 'array', 'request_headers' => 'array', 'response_headers' => 'array', 'sequence' => 'integer', 'response_status' => 'integer', 'duration_ms' => 'integer', 'attempt' => 'integer', 'requested_at' => 'datetime', 'responded_at' => 'datetime'];
    }

    /** @return BelongsTo<ProviderImportRun, $this> */
    public function run(): BelongsTo
    {
        return $this->belongsTo(ProviderImportRun::class, 'provider_import_run_id');
    }
}
