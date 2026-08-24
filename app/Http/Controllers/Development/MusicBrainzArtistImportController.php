<?php

declare(strict_types=1);

namespace App\Http\Controllers\Development;

use App\Http\Controllers\Controller;
use App\Support\Providers\Catalog\MusicBrainzArtistWorkbench;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

final class MusicBrainzArtistImportController extends Controller
{
    public function __invoke(Request $request, MusicBrainzArtistWorkbench $workbench): RedirectResponse
    {
        $validated = $request->validate([
            'mbid' => ['required', 'string', 'uuid'],
        ]);

        $run = $workbench->import((string) $validated['mbid']);

        return redirect()->route('development.status')
            ->with('development_notice', 'MusicBrainz artist import queued: '.$run->getKey());
    }
}
