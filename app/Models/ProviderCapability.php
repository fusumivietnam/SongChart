<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class ProviderCapability extends Model
{
    use HasUlids;

    protected $fillable = ['provider_id', 'capability', 'status', 'requires_user_consent', 'market_dependent', 'cache_ttl_seconds', 'configuration'];

    protected function casts(): array
    {
        return ['requires_user_consent' => 'boolean', 'market_dependent' => 'boolean', 'configuration' => 'array'];
    }

    /** @return BelongsTo<Provider, $this> */
    public function provider(): BelongsTo
    {
        return $this->belongsTo(Provider::class);
    }
}
