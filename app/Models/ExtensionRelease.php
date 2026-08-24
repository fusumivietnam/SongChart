<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class ExtensionRelease extends Model
{
    use HasUlids;

    protected $fillable = ['extension_id', 'version', 'path', 'checksum_sha256', 'signature_status', 'status', 'manifest', 'installed_at'];

    protected function casts(): array
    {
        return ['manifest' => 'array', 'installed_at' => 'datetime'];
    }

    /** @return BelongsTo<Extension, $this> */
    public function extension(): BelongsTo
    {
        return $this->belongsTo(Extension::class);
    }
}
