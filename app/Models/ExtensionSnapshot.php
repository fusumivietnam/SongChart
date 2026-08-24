<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class ExtensionSnapshot extends Model
{
    use HasUlids;

    protected $fillable = ['extension_id', 'reason', 'active_version', 'active_path', 'enabled', 'manifest', 'configuration', 'created_by'];

    protected function casts(): array
    {
        return ['enabled' => 'boolean', 'manifest' => 'array', 'configuration' => 'array'];
    }

    /** @return BelongsTo<Extension, $this> */
    public function extension(): BelongsTo
    {
        return $this->belongsTo(Extension::class);
    }
}
