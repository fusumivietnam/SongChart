<?php

declare(strict_types=1);

namespace App\Http\Controllers\Search;

use App\Application\Catalog\Queries\RecordingMediaExperience;
use App\Domain\Catalog\Enums\EntityType;
use App\Http\Controllers\Controller;
use App\Support\Catalog\PublicEntityUrl;
use App\Support\Search\DemoSearchCatalog;
use App\Support\Search\EloquentSearchCatalog;
use Illuminate\Contracts\View\View;

final class EntityController extends Controller
{
    public const ARTIST_SHOW_USE_CASE = 'public.artists.show';

    public const GROUP_SHOW_USE_CASE = 'public.groups.show';

    public const RELEASE_GROUP_SHOW_USE_CASE = 'public.release-groups.show';

    public const RELEASE_SHOW_USE_CASE = 'public.releases.show';

    public const RECORDING_SHOW_USE_CASE = 'public.recordings.show';

    public const WORK_SHOW_USE_CASE = 'public.works.show';

    public const VERSION_SHOW_USE_CASE = 'public.versions.show';

    public const COLLECTION_SHOW_USE_CASE = 'public.collections.show';

    public function artist(string $slug, EloquentSearchCatalog $catalog, DemoSearchCatalog $demo): View
    {
        $entity = $this->find(EntityType::Artist, $slug, $catalog, $demo);
        abort_if($entity === null || PublicEntityUrl::isGroupArtistType((string) ($entity['artist_type'] ?? '')), 404);

        return view('entities.show', compact('entity'));
    }

    public function group(string $slug, EloquentSearchCatalog $catalog, DemoSearchCatalog $demo): View
    {
        $entity = $this->find(EntityType::Artist, $slug, $catalog, $demo);
        abort_if($entity === null || ! PublicEntityUrl::isGroupArtistType((string) ($entity['artist_type'] ?? '')), 404);

        return view('entities.show', compact('entity'));
    }

    public function releaseGroup(string $slug, EloquentSearchCatalog $catalog, DemoSearchCatalog $demo): View
    {
        return $this->show(EntityType::ReleaseGroup, $slug, $catalog, $demo);
    }

    public function release(string $slug, EloquentSearchCatalog $catalog, DemoSearchCatalog $demo): View
    {
        return $this->show(EntityType::Release, $slug, $catalog, $demo);
    }

    public function recording(string $slug, EloquentSearchCatalog $catalog, DemoSearchCatalog $demo, RecordingMediaExperience $mediaExperience): View
    {
        $entity = $this->find(EntityType::Recording, $slug, $catalog, $demo);
        abort_if($entity === null, 404);

        $media = $mediaExperience->forSlug($slug);

        return view('entities.show', compact('entity', 'media'));
    }

    public function work(string $slug, EloquentSearchCatalog $catalog, DemoSearchCatalog $demo): View
    {
        return $this->show(EntityType::Work, $slug, $catalog, $demo);
    }

    public function version(string $slug, EloquentSearchCatalog $catalog, DemoSearchCatalog $demo): View
    {
        return $this->show(EntityType::Version, $slug, $catalog, $demo);
    }

    public function collection(string $slug, EloquentSearchCatalog $catalog, DemoSearchCatalog $demo): View
    {
        return $this->show(EntityType::Collection, $slug, $catalog, $demo);
    }

    private function show(EntityType $type, string $slug, EloquentSearchCatalog $catalog, DemoSearchCatalog $demo): View
    {
        $entity = $this->find($type, $slug, $catalog, $demo);
        abort_if($entity === null, 404);

        return view('entities.show', compact('entity'));
    }

    /** @return array<string, mixed>|null */
    private function find(EntityType $type, string $slug, EloquentSearchCatalog $catalog, DemoSearchCatalog $demo): ?array
    {
        $entity = $catalog->find($type->value, $slug);
        if ($entity !== null || ! app()->environment(['local', 'testing'])) {
            return $entity;
        }

        return $demo->find($type->value, $slug);
    }
}
