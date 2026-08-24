<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;

final class TrustedPublisher extends Model
{
    use HasUlids;

    protected $fillable = ['name', 'slug', 'public_key', 'status', 'fingerprint', 'valid_from', 'valid_until', 'revoked_at', 'metadata'];

    protected function casts(): array
    {
        return ['valid_from' => 'datetime', 'valid_until' => 'datetime', 'revoked_at' => 'datetime', 'metadata' => 'array'];
    }
}
