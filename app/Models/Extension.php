<?php

declare(strict_types=1);

namespace App\Models;

use App\Domain\Extensions\Enums\ExtensionState;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class Extension extends Model
{
    use HasUlids;

    protected $fillable = ['type', 'slug', 'name', 'state', 'active_version', 'active_path', 'enabled', 'manifest', 'configuration', 'health_status', 'last_health_checked_at', 'installed_at'];

    protected function casts(): array
    {
        return [
            'state' => ExtensionState::class,
            'enabled' => 'boolean',
            'manifest' => 'array',
            'configuration' => 'array',
            'last_health_checked_at' => 'datetime',
            'installed_at' => 'datetime',
        ];
    }

    /** @return HasMany<ExtensionRelease, $this> */
    public function releases(): HasMany
    {
        return $this->hasMany(ExtensionRelease::class);
    }

    /** @return HasMany<ExtensionOperation, $this> */
    public function operations(): HasMany
    {
        return $this->hasMany(ExtensionOperation::class);
    }

    /** @return HasMany<ExtensionSnapshot, $this> */
    public function snapshots(): HasMany
    {
        return $this->hasMany(ExtensionSnapshot::class);
    }
}
