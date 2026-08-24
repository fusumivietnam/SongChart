<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\Providers\Catalog\MusicBrainzRecordingWorkbench;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

final class MusicBrainzRecordingImportController extends Controller
{
    public const IMPORT_USE_CASE = 'admin.providers.musicbrainz.recordings.import';

    public function __invoke(Request $request, string $provider, MusicBrainzRecordingWorkbench $workbench): RedirectResponse
    {
        $validated = $request->validate(['mbid' => ['required', 'string', 'uuid']]);
        $run = $workbench->importForProvider($provider, (string) $validated['mbid']);

        return redirect()->route('admin.providers.show', ['provider' => $provider])
            ->with('status', 'MusicBrainz recording import queued: '.$run->getKey());
    }
}
