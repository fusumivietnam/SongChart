<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\Providers\Catalog\MusicBrainzArtistWorkbench;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

final class MusicBrainzArtistImportController extends Controller
{
    public const IMPORT_USE_CASE = 'admin.providers.musicbrainz.artists.import';

    public function __invoke(Request $request, string $provider, MusicBrainzArtistWorkbench $workbench): RedirectResponse
    {
        $validated = $request->validate([
            'mbid' => ['required', 'string', 'uuid'],
        ]);

        $run = $workbench->importForProvider($provider, (string) $validated['mbid']);

        return redirect()->route('admin.providers.show', ['provider' => $provider])
            ->with('status', 'MusicBrainz artist import queued: '.$run->getKey());
    }
}
