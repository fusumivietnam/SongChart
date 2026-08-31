<?php

declare(strict_types=1);

namespace App\Models;

use App\Domain\Providers\Enums\ProviderCategory;
use App\Domain\Providers\Enums\ProviderStatus;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use InvalidArgumentException;

/**
 * @property ProviderStatus $status
 * @property bool $is_enabled
 * @property array<string, mixed>|null $configuration
 */
final class Provider extends Model
{
    use HasUlids;

    protected $fillable = ['slug', 'name', 'category', 'status', 'is_enabled', 'feature_flag', 'official_docs_url', 'policy_reviewed_at', 'configuration'];

    protected static function booted(): void
    {
        self::saving(function (self $provider): void {
            $category = (string) $provider->getAttribute('category');

            if (ProviderCategory::tryFrom($category) === null) {
                throw new InvalidArgumentException("Unsupported provider category [{$category}].");
            }
        });
    }

    protected function casts(): array
    {
        return [
            'status' => ProviderStatus::class,
            'is_enabled' => 'boolean',
            'policy_reviewed_at' => 'date',
            'configuration' => 'array',
        ];
    }

    /** @return HasMany<ProviderCapability, $this> */
    public function capabilities(): HasMany
    {
        return $this->hasMany(ProviderCapability::class);
    }
}
