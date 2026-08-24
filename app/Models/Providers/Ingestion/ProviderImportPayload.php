<?php

declare(strict_types=1);

namespace App\Models\Providers\Ingestion;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use LogicException;

final class ProviderImportPayload extends Model
{
    use HasUlids;

    protected $table = 'provider_import_payloads';

    protected $fillable = ['provider_import_run_id', 'provider_import_request_id', 'provider_entity_type', 'provider_entity_id', 'payload_hash', 'payload', 'schema_version', 'received_at'];

    protected function casts(): array
    {
        return ['payload' => 'array', 'received_at' => 'datetime'];
    }

    protected static function booted(): void
    {
        self::updating(fn () => throw new LogicException('Raw provider payloads are immutable.'));
        self::deleting(fn () => throw new LogicException('Raw provider payloads cannot be deleted individually.'));
    }

    /** @return BelongsTo<ProviderImportRun, $this> */
    public function run(): BelongsTo
    {
        return $this->belongsTo(ProviderImportRun::class, 'provider_import_run_id');
    }

    /** @return BelongsTo<ProviderImportRequest, $this> */
    public function request(): BelongsTo
    {
        return $this->belongsTo(ProviderImportRequest::class, 'provider_import_request_id');
    }
}
