<?php

declare(strict_types=1);

namespace App\Http\Controllers\Development;

use App\Http\Controllers\Controller;
use App\Support\Local\DevelopmentControlCenter;
use App\Support\Local\LocalReadinessReport;
use App\Support\Providers\Catalog\MusicBrainzArtistWorkbench;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Throwable;

final class StatusController extends Controller
{
    public function __invoke(Request $request, LocalReadinessReport $report, DevelopmentControlCenter $control, MusicBrainzArtistWorkbench $workbench): View
    {
        $musicBrainzResults = [];
        $musicBrainzError = null;
        $query = trim((string) $request->query('musicbrainz_query', ''));
        if ($query !== '') {
            try {
                $musicBrainzResults = $workbench->search($query);
            } catch (Throwable $exception) {
                $musicBrainzError = $exception->getMessage();
            }
        }

        return view('development.status', [
            'checks' => $report->checks(),
            'providers' => $control->providers(),
            'pipeline' => $control->pipeline(),
            'musicBrainzQuery' => $query,
            'musicBrainzResults' => $musicBrainzResults,
            'musicBrainzError' => $musicBrainzError,
            'urls' => [
                'Home' => route('home'),
                'Login' => route('login'),
                'Account' => route('account.overview'),
                'Account security' => route('account.security'),
                'Admin' => route('admin.dashboard'),
                'Providers' => route('admin.providers.index'),
                'Imports' => route('admin.imports.index'),
                'Identity conflicts' => route('admin.identity-conflicts.index'),
                'Design system' => route('development.design-system.index'),
                'Development status' => route('development.status'),
            ],
        ]);
    }
}
