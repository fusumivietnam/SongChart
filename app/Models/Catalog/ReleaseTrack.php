<?php

declare(strict_types=1);

namespace App\Models\Catalog;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;

final class ReleaseTrack extends Model
{
    use HasUlids;

    protected $table = 'release_tracks';

    protected $fillable = ['release_id', 'recording_id', 'disc_number', 'track_number', 'title_override'];
}
