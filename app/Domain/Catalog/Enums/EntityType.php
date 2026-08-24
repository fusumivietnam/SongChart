<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Enums;

use App\Models\Catalog\Artist;
use App\Models\Catalog\Collection;
use App\Models\Catalog\Recording;
use App\Models\Catalog\RecordingVersion;
use App\Models\Catalog\Release;
use App\Models\Catalog\ReleaseGroup;
use App\Models\Catalog\Work;
use Illuminate\Database\Eloquent\Model;

enum EntityType: string
{
    case Artist = 'artist';
    case Work = 'work';
    case Recording = 'recording';
    case ReleaseGroup = 'release_group';
    case Release = 'release';
    case Version = 'version';
    case Collection = 'collection';

    public static function routePattern(): string
    {
        return implode('|', array_column(self::cases(), 'value'));
    }

    /** @return class-string<Model> */
    public function modelClass(): string
    {
        return match ($this) {
            self::Artist => Artist::class,
            self::Work => Work::class,
            self::Recording => Recording::class,
            self::ReleaseGroup => ReleaseGroup::class,
            self::Release => Release::class,
            self::Version => RecordingVersion::class,
            self::Collection => Collection::class,
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::Artist => 'Nghệ sĩ',
            self::Work => 'Tác phẩm',
            self::Recording => 'Bản thu',
            self::ReleaseGroup => 'Nhóm phát hành',
            self::Release => 'Phát hành',
            self::Version => 'Phiên bản',
            self::Collection => 'Bộ sưu tập',
        };
    }
}
