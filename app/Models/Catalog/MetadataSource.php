<?php

declare(strict_types=1);

namespace App\Models\Catalog;

use Database\Factories\Catalog\MetadataSourceFactory;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

final class MetadataSource extends Model
{
    /** @use HasFactory<MetadataSourceFactory> */
    use HasFactory;

    use HasUlids;

    protected $table = 'metadata_sources';

    protected $fillable = ['provider_id', 'key', 'name', 'source_type', 'reference_url', 'license_name', 'retention_policy'];
}
