<?php

declare(strict_types=1);

namespace App\Models;

use App\Domain\Providers\Enums\ProviderCapabilityCode;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use InvalidArgumentException;

final class ProviderCapability extends Model
{
    use HasUlids;

    protected $fillable = ['provider_id', 'capability', 'status', 'requires_user_consent', 'market_dependent', 'cache_ttl_seconds', 'configuration'];

    protected static function booted(): void
    {
        self::saving(function (ProviderCapability $capability): void {
            $code = (string) $capability->getAttribute('capability');

            if (ProviderCapabilityCode::tryFrom($code) === null) {
                throw new InvalidArgumentException("Unsupported provider capability [{$code}].");
            }
        });
    }

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
