<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\Providers\Catalog\MusicBrainzWorkWorkbench;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

final class MusicBrainzWorkImportController extends Controller
{
    public const IMPORT_USE_CASE = 'admin.providers.musicbrainz.works.import';

    public function __invoke(Request $request, string $provider, MusicBrainzWorkWorkbench $workbench): RedirectResponse
    {
        $validated = $request->validate(['mbid' => ['required', 'string', 'uuid']]);
        $run = $workbench->importForProvider($provider, (string) $validated['mbid']);

        return redirect()->route('admin.providers.show', ['provider' => $provider])
            ->with('status', 'MusicBrainz work import queued: '.$run->getKey());
    }
}
