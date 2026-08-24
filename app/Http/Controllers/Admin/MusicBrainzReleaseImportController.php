<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\Providers\Catalog\MusicBrainzReleaseWorkbench;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

final class MusicBrainzReleaseImportController extends Controller
{
    public const IMPORT_USE_CASE = 'admin.providers.musicbrainz.releases.import';

    public function __invoke(Request $request, string $provider, MusicBrainzReleaseWorkbench $workbench): RedirectResponse
    {
        $validated = $request->validate([
            'entity_type' => ['required', 'string', Rule::in(['release_group', 'release'])],
            'mbid' => ['required', 'string', 'uuid'],
        ]);

        $run = $validated['entity_type'] === 'release_group'
            ? $workbench->importReleaseGroupForProvider($provider, (string) $validated['mbid'])
            : $workbench->importReleaseForProvider($provider, (string) $validated['mbid']);

        return redirect()->route('admin.providers.show', ['provider' => $provider])
            ->with('status', 'MusicBrainz '.str_replace('_', ' ', (string) $validated['entity_type']).' import queued: '.$run->getKey());
    }
}
