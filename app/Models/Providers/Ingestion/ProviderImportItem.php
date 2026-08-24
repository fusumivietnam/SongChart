<?php

declare(strict_types=1);

namespace App\Models\Providers\Ingestion;

use App\Domain\Providers\Ingestion\Enums\ProviderImportItemStatus;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class ProviderImportItem extends Model
{
    use HasUlids;

    protected $table = 'provider_import_items';

    protected $fillable = ['provider_import_run_id', 'provider_import_payload_id', 'provider_entity_type', 'provider_entity_id', 'status', 'canonical_entity_type', 'canonical_entity_id', 'normalizer_version', 'attempts', 'result', 'processed_at'];

    protected function casts(): array
    {
        return ['status' => ProviderImportItemStatus::class, 'attempts' => 'integer', 'result' => 'array', 'processed_at' => 'datetime'];
    }

    /** @return BelongsTo<ProviderImportRun, $this> */
    public function run(): BelongsTo
    {
        return $this->belongsTo(ProviderImportRun::class, 'provider_import_run_id');
    }

    /** @return BelongsTo<ProviderImportPayload, $this> */
    public function payload(): BelongsTo
    {
        return $this->belongsTo(ProviderImportPayload::class, 'provider_import_payload_id');
    }
}
