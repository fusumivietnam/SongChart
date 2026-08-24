<?php

declare(strict_types=1);

namespace App\Http\Controllers\Search;

use App\Domain\Catalog\Enums\EntityType;
use App\Http\Controllers\Controller;
use App\Support\Search\EloquentSearchCatalog;
use Illuminate\Contracts\View\View;

final class EntityController extends Controller
{
    public const ARTIST_SHOW_USE_CASE = 'public.artists.show';

    public const RELEASE_GROUP_SHOW_USE_CASE = 'public.release-groups.show';

    public const RELEASE_SHOW_USE_CASE = 'public.releases.show';

    public const RECORDING_SHOW_USE_CASE = 'public.recordings.show';

    public const WORK_SHOW_USE_CASE = 'public.works.show';

    public const VERSION_SHOW_USE_CASE = 'public.versions.show';

    public const COLLECTION_SHOW_USE_CASE = 'public.collections.show';

    public function artist(string $slug, EloquentSearchCatalog $catalog): View
    {
        return $this->show(EntityType::Artist, $slug, $catalog);
    }

    public function releaseGroup(string $slug, EloquentSearchCatalog $catalog): View
    {
        return $this->show(EntityType::ReleaseGroup, $slug, $catalog);
    }

    public function release(string $slug, EloquentSearchCatalog $catalog): View
    {
        return $this->show(EntityType::Release, $slug, $catalog);
    }

    public function recording(string $slug, EloquentSearchCatalog $catalog): View
    {
        return $this->show(EntityType::Recording, $slug, $catalog);
    }

    public function work(string $slug, EloquentSearchCatalog $catalog): View
    {
        return $this->show(EntityType::Work, $slug, $catalog);
    }

    public function version(string $slug, EloquentSearchCatalog $catalog): View
    {
        return $this->show(EntityType::Version, $slug, $catalog);
    }

    public function collection(string $slug, EloquentSearchCatalog $catalog): View
    {
        return $this->show(EntityType::Collection, $slug, $catalog);
    }

    private function show(EntityType $type, string $slug, EloquentSearchCatalog $catalog): View
    {
        $entity = $catalog->find($type->value, $slug);
        abort_if($entity === null, 404);

        return view('entities.show', compact('entity'));
    }
}
