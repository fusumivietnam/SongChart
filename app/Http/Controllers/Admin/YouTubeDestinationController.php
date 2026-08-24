<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ApproveYouTubeDestinationRequest;
use App\Support\Providers\Destinations\YouTubeDestinationWorkbench;
use Illuminate\Http\RedirectResponse;

final class YouTubeDestinationController extends Controller
{
    public const APPROVE_USE_CASE = 'admin.providers.youtube.destinations.approve';

    public function __invoke(string $provider, ApproveYouTubeDestinationRequest $request, YouTubeDestinationWorkbench $workbench): RedirectResponse
    {
        /** @var array{recording_id:string,video_id:string} $validated */
        $validated = $request->validated();
        $workbench->approve($provider, $validated['recording_id'], $validated['video_id']);

        return redirect()->route('admin.providers.show', ['provider' => $provider, 'youtube_recording_id' => $validated['recording_id']])
            ->with('status', 'YouTube destination was verified and approved for the canonical Recording.');
    }
}
