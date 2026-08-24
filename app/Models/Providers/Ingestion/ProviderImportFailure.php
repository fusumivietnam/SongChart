<?php

declare(strict_types=1);

namespace App\Models\Providers\Ingestion;

use App\Domain\Providers\Ingestion\Enums\ProviderImportFailureStage;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class ProviderImportFailure extends Model
{
    use HasUlids;

    protected $table = 'provider_import_failures';

    protected $fillable = ['provider_import_run_id', 'provider_import_item_id', 'provider_import_request_id', 'stage', 'kind', 'error_code', 'message', 'context', 'retryable', 'occurred_at'];

    protected function casts(): array
    {
        return ['stage' => ProviderImportFailureStage::class, 'context' => 'array', 'retryable' => 'boolean', 'occurred_at' => 'datetime'];
    }

    /** @return BelongsTo<ProviderImportRun, $this> */
    public function run(): BelongsTo
    {
        return $this->belongsTo(ProviderImportRun::class, 'provider_import_run_id');
    }
}
