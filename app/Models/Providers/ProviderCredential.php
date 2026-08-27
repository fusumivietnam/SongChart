<?php

declare(strict_types=1);

namespace App\Models\Providers;

use App\Models\Provider;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class ProviderCredential extends Model
{
    use HasUlids;

    protected $fillable = [
        'provider_id',
        'kind',
        'label',
        'encrypted_secret',
        'is_enabled',
        'priority',
        'cooldown_until',
        'last_used_at',
        'failure_count',
    ];

    protected $hidden = ['encrypted_secret'];

    protected function casts(): array
    {
        return [
            'is_enabled' => 'boolean',
            'priority' => 'integer',
            'cooldown_until' => 'datetime',
            'last_used_at' => 'datetime',
            'failure_count' => 'integer',
        ];
    }

    /** @return BelongsTo<Provider, $this> */
    public function provider(): BelongsTo
    {
        return $this->belongsTo(Provider::class);
    }
}
