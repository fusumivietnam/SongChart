<?php

declare(strict_types=1);

namespace App\Http\Controllers\PublicCatalog;

use App\Application\Catalog\Queries\PublicCatalogReadModel;
use App\Domain\Catalog\Enums\EntityType;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

final class BrowseController extends Controller
{
    public const ARTISTS_INDEX_USE_CASE = 'public.artists.index';

    public const GROUPS_INDEX_USE_CASE = 'public.groups.index';

    public const RELEASES_INDEX_USE_CASE = 'public.releases.index';

    public const RECORDINGS_INDEX_USE_CASE = 'public.recordings.index';

    public const WORKS_INDEX_USE_CASE = 'public.works.index';

    public const COLLECTIONS_INDEX_USE_CASE = 'public.collections.index';

    public function artists(Request $request, PublicCatalogReadModel $catalog): View
    {
        return view('catalog.index', $catalog->index(EntityType::Artist, (string) $request->query('q', ''), 'solo'));
    }

    public function groups(Request $request, PublicCatalogReadModel $catalog): View
    {
        return view('catalog.index', $catalog->index(EntityType::Artist, (string) $request->query('q', ''), 'group'));
    }

    public function releases(Request $request, PublicCatalogReadModel $catalog): View
    {
        return view('catalog.index', $catalog->index(EntityType::Release, (string) $request->query('q', '')));
    }

    public function recordings(Request $request, PublicCatalogReadModel $catalog): View
    {
        return view('catalog.index', $catalog->index(EntityType::Recording, (string) $request->query('q', '')));
    }

    public function works(Request $request, PublicCatalogReadModel $catalog): View
    {
        return view('catalog.index', $catalog->index(EntityType::Work, (string) $request->query('q', '')));
    }

    public function collections(Request $request, PublicCatalogReadModel $catalog): View
    {
        return view('catalog.index', $catalog->index(EntityType::Collection, (string) $request->query('q', '')));
    }
}
