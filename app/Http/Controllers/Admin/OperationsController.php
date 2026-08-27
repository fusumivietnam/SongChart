<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Contracts\Providers\Rate\ProviderRatePolicyRegistry;
use App\Contracts\Providers\Rate\ProviderRequestGate;
use App\Http\Controllers\Controller;
use App\Support\Admin\AdminInformationArchitecture;
use App\Support\Admin\AdminOperationsPresentation;
use App\Support\Admin\ProviderOperationsConsole;
use App\Support\Providers\Catalog\MusicBrainzArtistWorkbench;
use App\Support\Providers\Catalog\MusicBrainzRecordingWorkbench;
use App\Support\Providers\Catalog\MusicBrainzReleaseWorkbench;
use App\Support\Providers\Catalog\MusicBrainzWorkWorkbench;
use App\Support\Providers\Configuration\ProviderRuntimeConfiguration;
use App\Support\Providers\Destinations\YouTubeDestinationWorkbench;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Throwable;

final class OperationsController extends Controller
{
    public const PROVIDERS_USE_CASE = 'admin.providers.index';

    public const PROVIDER_SHOW_USE_CASE = 'admin.providers.show';

    public const IMPORTS_USE_CASE = 'admin.imports.index';

    public const IMPORT_SHOW_USE_CASE = 'admin.imports.show';

    public const QUARANTINE_USE_CASE = 'admin.quarantine.index';

    public const USERS_USE_CASE = 'admin.users.index';

    public function catalog(AdminInformationArchitecture $information): View
    {
        return view('admin.operations.catalog', $information->catalog());
    }

    public function providers(Request $request, ProviderOperationsConsole $console, AdminOperationsPresentation $presentation): View
    {
        return view('admin.operations.providers', [
            ...$console->providers($request->query()),
            'presentation' => $presentation,
        ]);
    }

    public function provider(
        string $provider,
        Request $request,
        ProviderOperationsConsole $console,
        AdminOperationsPresentation $presentation,
        MusicBrainzArtistWorkbench $workbench,
        MusicBrainzReleaseWorkbench $releaseWorkbench,
        MusicBrainzRecordingWorkbench $recordingWorkbench,
        MusicBrainzWorkWorkbench $workWorkbench,
        YouTubeDestinationWorkbench $youtubeWorkbench,
        ProviderRatePolicyRegistry $ratePolicies,
        ProviderRequestGate $requestGate,
        ProviderRuntimeConfiguration $runtimeConfiguration,
    ): View {
        $data = $console->provider($provider);
        $providerSlug = (string) ($data['provider']->slug ?? '');
        $runtimeConfiguration->apply($providerSlug);

        $musicBrainzQuery = trim((string) $request->query('musicbrainz_query', ''));
        $musicBrainzResults = [];
        $musicBrainzReleaseGroupQuery = trim((string) $request->query('musicbrainz_release_group_query', ''));
        $musicBrainzReleaseQuery = trim((string) $request->query('musicbrainz_release_query', ''));
        $musicBrainzReleaseGroupResults = [];
        $musicBrainzReleaseResults = [];
        $musicBrainzRecordingQuery = trim((string) $request->query('musicbrainz_recording_query', ''));
        $musicBrainzRecordingResults = [];
        $musicBrainzWorkQuery = trim((string) $request->query('musicbrainz_work_query', ''));
        $musicBrainzWorkResults = [];
        $musicBrainzError = null;
        $providerRateState = null;
        $youtubeRecordingId = trim((string) $request->query('youtube_recording_id', ''));
        $youtubeRecording = null;
        $youtubeCandidates = [];
        $youtubeError = null;

        if ($providerSlug === 'musicbrainz') {
            $providerRateState = $requestGate->state($ratePolicies->for('musicbrainz', 'artist.search'));
        }

        if ($providerSlug === 'musicbrainz') {
            try {
                if ($musicBrainzQuery !== '') {
                    $musicBrainzResults = $workbench->search($musicBrainzQuery);
                } elseif ($musicBrainzReleaseGroupQuery !== '') {
                    $musicBrainzReleaseGroupResults = $releaseWorkbench->searchReleaseGroups($musicBrainzReleaseGroupQuery);
                } elseif ($musicBrainzReleaseQuery !== '') {
                    $musicBrainzReleaseResults = $releaseWorkbench->searchReleases($musicBrainzReleaseQuery);
                } elseif ($musicBrainzRecordingQuery !== '') {
                    $musicBrainzRecordingResults = $recordingWorkbench->search($musicBrainzRecordingQuery);
                } elseif ($musicBrainzWorkQuery !== '') {
                    $musicBrainzWorkResults = $workWorkbench->search($musicBrainzWorkQuery);
                }
            } catch (Throwable $exception) {
                $musicBrainzError = $exception->getMessage();
            }
        }

        if ($providerSlug === 'youtube' && $youtubeRecordingId !== '') {
            try {
                $youtube = $youtubeWorkbench->search($youtubeRecordingId);
                $youtubeRecording = $youtube['recording'];
                $youtubeCandidates = $youtube['candidates'];
            } catch (Throwable $exception) {
                $youtubeError = $exception->getMessage();
            }
        }

        return view('admin.operations.provider-show', [
            ...$data,
            'presentation' => $presentation,
            'musicBrainzQuery' => $musicBrainzQuery,
            'musicBrainzResults' => $musicBrainzResults,
            'musicBrainzReleaseGroupQuery' => $musicBrainzReleaseGroupQuery,
            'musicBrainzReleaseQuery' => $musicBrainzReleaseQuery,
            'musicBrainzReleaseGroupResults' => $musicBrainzReleaseGroupResults,
            'musicBrainzReleaseResults' => $musicBrainzReleaseResults,
            'musicBrainzRecordingQuery' => $musicBrainzRecordingQuery,
            'musicBrainzRecordingResults' => $musicBrainzRecordingResults,
            'musicBrainzWorkQuery' => $musicBrainzWorkQuery,
            'musicBrainzWorkResults' => $musicBrainzWorkResults,
            'musicBrainzError' => $musicBrainzError,
            'providerRateState' => $providerRateState,
            'youtubeRecordingId' => $youtubeRecordingId,
            'youtubeRecording' => $youtubeRecording,
            'youtubeCandidates' => $youtubeCandidates,
            'youtubeError' => $youtubeError,
        ]);
    }

    public function imports(Request $request, ProviderOperationsConsole $console, AdminOperationsPresentation $presentation): View
    {
        return view('admin.operations.imports', [
            ...$console->imports($request->query()),
            'presentation' => $presentation,
        ]);
    }

    public function importRun(string $run, ProviderOperationsConsole $console, AdminOperationsPresentation $presentation): View
    {
        return view('admin.operations.import-show', [
            ...$console->importRun($run),
            'presentation' => $presentation,
        ]);
    }

    public function quarantine(Request $request, ProviderOperationsConsole $console): View
    {
        return view('admin.operations.quarantine', $console->quarantine($request->query()));
    }

    public function users(AdminInformationArchitecture $information): View
    {
        return view('admin.operations.users', $information->users());
    }

    public function system(
        AdminInformationArchitecture $information,
        ProviderOperationsConsole $console,
        ProviderRuntimeConfiguration $runtimeConfiguration,
    ): View {
        $providerData = $console->providers([]);

        return view('admin.operations.system', [
            ...$information->system(),
            'providers' => $providerData['providers'],
            'runtimeConfiguration' => $runtimeConfiguration,
        ]);
    }
}
