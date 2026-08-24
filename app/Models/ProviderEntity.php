<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class ProviderEntity extends Model
{
    use HasUlids;

    protected $fillable = ['provider_id', 'entity_type', 'external_id', 'canonical_url', 'market', 'raw_fingerprint', 'fetched_at', 'expires_at', 'status'];

    protected function casts(): array
    {
        return ['fetched_at' => 'datetime', 'expires_at' => 'datetime'];
    }

    /** @return BelongsTo<Provider, $this> */
    public function provider(): BelongsTo
    {
        return $this->belongsTo(Provider::class);
    }
}
