<?php

declare(strict_types=1);

namespace App\Application\Catalog\Queries;

use App\Domain\Catalog\Enums\EntityType;
use App\Models\Catalog\Artist;
use App\Models\Catalog\Collection;
use App\Models\Catalog\Recording;
use App\Models\Catalog\RecordingVersion;
use App\Models\Catalog\Release;
use App\Models\Catalog\ReleaseGroup;
use App\Models\Catalog\Work;
use App\Support\Catalog\PublicEntityUrl;
use Illuminate\Database\Eloquent\Model;

final class PublicSitemap
{
    /** @return list<string> */
    public function urls(): array
    {
        $urls = [
            route('home'),
            route('privacy'),
            route('artists.index'),
            route('groups.index'),
            route('releases.index'),
            route('recordings.index'),
            route('works.index'),
            route('collections.index'),
        ];

        Artist::query()
            ->select(['id', 'slug', 'artist_type'])
            ->orderBy('id')
            ->each(function (Artist $artist) use (&$urls): void {
                $urls[] = PublicEntityUrl::to(
                    EntityType::Artist->value,
                    (string) $artist->slug,
                    (string) $artist->artist_type,
                );
            });

        $this->appendCanonicalUrls($urls, EntityType::ReleaseGroup, ReleaseGroup::class);
        $this->appendCanonicalUrls($urls, EntityType::Release, Release::class);
        $this->appendCanonicalUrls($urls, EntityType::Recording, Recording::class);
        $this->appendCanonicalUrls($urls, EntityType::Work, Work::class);
        $this->appendCanonicalUrls($urls, EntityType::Version, RecordingVersion::class);

        Collection::query()
            ->where('visibility', 'public')
            ->select(['id', 'slug'])
            ->orderBy('id')
            ->each(function (Collection $collection) use (&$urls): void {
                $urls[] = PublicEntityUrl::to(EntityType::Collection->value, (string) $collection->slug);
            });

        return array_values(array_unique($urls));
    }

    /**
     * @param  list<string>  $urls
     * @param  class-string<Model>  $modelClass
     */
    private function appendCanonicalUrls(array &$urls, EntityType $type, string $modelClass): void
    {
        $modelClass::query()
            ->select(['id', 'slug'])
            ->orderBy('id')
            ->each(function (Model $model) use (&$urls, $type): void {
                $urls[] = PublicEntityUrl::to($type->value, (string) $model->getAttribute('slug'));
            });
    }
}
