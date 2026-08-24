@extends('layouts.admin')
@section('content')
@php($state = $presentation->providerStatus($provider->status, $provider->is_enabled))
<x-admin.page-header :title="$title" :description="$description" />
@if(session('status'))<div class="mt-4 rounded border p-3 text-sm">{{ session('status') }}</div>@endif

<div class="mt-6 grid gap-6 xl:grid-cols-[1fr_.8fr]" data-admin-section="provider-detail">
    <x-ui.card><div class="p-5"><div class="flex flex-wrap items-start justify-between gap-3"><div><p class="text-sm text-slate-500">Tình trạng nguồn</p><div class="mt-2"><x-ui.badge :variant="$state['tone']">{{ $state['label'] }}</x-ui.badge></div></div><div class="text-right text-sm text-slate-500">Rà soát chính sách<br><strong class="text-slate-900">{{ $provider->policy_reviewed_at?->format('d/m/Y') ?? 'Chưa ghi nhận' }}</strong></div></div><dl class="mt-6 grid gap-4 text-sm sm:grid-cols-2"><div><dt class="text-slate-500">Nguồn</dt><dd class="font-semibold">{{ $provider->name }}</dd></div><div><dt class="text-slate-500">Đang sử dụng</dt><dd>{{ $provider->is_enabled ? 'Có' : 'Không' }}</dd></div><div><dt class="text-slate-500">Tính năng được cấu hình</dt><dd>{{ $provider->feature_flag ?: 'Không yêu cầu' }}</dd></div><div><dt class="text-slate-500">Tài liệu chính thức</dt><dd>@if($provider->official_docs_url)<a class="underline" href="{{ $provider->official_docs_url }}" rel="noreferrer">Mở tài liệu</a>@else—@endif</dd></div></dl></div></x-ui.card>

    @can('manage-providers')
    <x-ui.card><div class="p-5"><h2 class="font-semibold">Thao tác</h2><p class="mt-1 text-sm text-slate-500">Chỉ dùng khi cần thay đổi trạng thái vận hành của nguồn dữ liệu.</p><form class="mt-4 space-y-3" method="POST" action="{{ route('admin.providers.mutate',$provider) }}">@csrf<select name="action" class="w-full rounded border p-2"><option value="enable">Tiếp tục sử dụng nguồn</option><option value="disable">Tạm ngừng nguồn</option><option value="retire">Ngừng sử dụng vĩnh viễn</option></select><label class="block text-sm font-medium">Lý do thay đổi<input name="rationale" required minlength="10" maxlength="2000" class="mt-1 w-full rounded border p-2" placeholder="Mô tả ngắn lý do và bối cảnh"></label><input type="hidden" name="idempotency_key" value="{{ (string) str()->uuid() }}"><button class="rounded border px-4 py-2 font-semibold">Xác nhận thao tác</button></form><p class="mt-3 text-xs text-slate-500">Ngừng sử dụng vĩnh viễn không xóa dữ liệu đã nhập hoặc lịch sử vận hành.</p></div></x-ui.card>
    @endcan
</div>


@if($provider->slug === 'musicbrainz')
<x-ui.card class="mt-6" data-admin-musicbrainz-workbench>
    <div class="p-5">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Provider import workbench</p>
                <h2 class="mt-1 text-lg font-semibold">MusicBrainz catalog search & import</h2>
                <p class="mt-1 max-w-3xl text-sm text-slate-500">Tìm Artist, Release Group và Release trực tiếp từ MusicBrainz rồi đưa MBID đã chọn vào cùng pipeline raw ledger → normalization → identity → canonical mutation.</p>
            </div>
            <a class="rounded border px-3 py-2 text-sm font-semibold" href="{{ route('admin.imports.index', ['provider' => $provider->getKey()]) }}">Xem imports</a>
        </div>

        @if($providerRateState ?? null)
            <div class="mt-4 grid gap-3 sm:grid-cols-3" data-provider-rate-state>
                <div class="rounded border p-3"><div class="text-xs uppercase tracking-wide text-slate-500">Rate strategy</div><strong>{{ $providerRateState->strategy }}</strong></div>
                <div class="rounded border p-3"><div class="text-xs uppercase tracking-wide text-slate-500">Minimum interval</div><strong>{{ $providerRateState->minimumIntervalMilliseconds }} ms</strong></div>
                <div class="rounded border p-3"><div class="text-xs uppercase tracking-wide text-slate-500">Gate</div><strong>{{ $providerRateState->status() }}</strong>@if($providerRateState->cooldownRemainingSeconds > 0)<div class="text-xs text-amber-700">Retry in {{ $providerRateState->cooldownRemainingSeconds }}s · {{ $providerRateState->cooldownReason ?? 'provider limit' }}</div>@endif</div>
            </div>
        @endif

        <form class="mt-5 flex flex-col gap-3 sm:flex-row" method="GET" action="{{ route('admin.providers.show', $provider) }}">
            <input name="musicbrainz_query" value="{{ $musicBrainzQuery ?? '' }}" class="min-w-0 flex-1 rounded border p-3" placeholder="Daft Punk, Radiohead, Taylor Swift…" required @disabled(($providerRateState?->cooldownRemainingSeconds ?? 0) > 0)>
            <button class="rounded border px-4 py-3 font-semibold disabled:cursor-not-allowed disabled:opacity-50" type="submit" @disabled(($providerRateState?->cooldownRemainingSeconds ?? 0) > 0)>Search live</button>
        </form>

        @if($musicBrainzError ?? null)
            <div class="mt-4 rounded border bg-amber-50 p-3 text-sm text-amber-950">{{ $musicBrainzError }}</div>
        @endif

        @if(($musicBrainzResults ?? []) !== [])
            <div class="mt-5 overflow-x-auto">
                <table class="w-full min-w-[760px] text-left text-sm">
                    <thead class="text-xs uppercase tracking-wide text-slate-500"><tr><th class="py-2 pr-3">Artist</th><th class="py-2 pr-3">Country/type</th><th class="py-2 pr-3">MBID</th><th class="py-2">Action</th></tr></thead>
                    <tbody class="divide-y">
                    @foreach($musicBrainzResults as $artist)
                        <tr>
                            <td class="py-3 pr-3"><strong>{{ $artist['name'] }}</strong>@if($artist['disambiguation'])<div class="text-xs text-slate-500">{{ $artist['disambiguation'] }}</div>@endif</td>
                            <td class="py-3 pr-3">{{ $artist['country'] ?: '—' }} · {{ $artist['type'] ?: '—' }}</td>
                            <td class="py-3 pr-3 font-mono text-xs">{{ $artist['id'] }}</td>
                            <td class="py-3">
                                @can('manage-providers')
                                    <form method="POST" action="{{ route('admin.providers.musicbrainz.artists.import', $provider) }}">
                                        @csrf
                                        <input type="hidden" name="mbid" value="{{ $artist['id'] }}">
                                        <button class="rounded border px-3 py-2 font-semibold" type="submit">Import Artist</button>
                                    </form>
                                @else
                                    <span class="text-xs text-slate-500">Read-only</span>
                                @endcan
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        <div class="mt-8 grid gap-6 xl:grid-cols-2" data-musicbrainz-release-workbench>
            <div class="rounded border p-4">
                <h3 class="font-semibold">Release Group search</h3>
                <p class="mt-1 text-sm text-slate-500">Logical Album / Single / EP identity. Import this before a specific Release when possible.</p>
                <form class="mt-4 flex gap-2" method="GET" action="{{ route('admin.providers.show', $provider) }}">
                    <input name="musicbrainz_release_group_query" value="{{ $musicBrainzReleaseGroupQuery ?? '' }}" class="min-w-0 flex-1 rounded border p-2" placeholder="Random Access Memories…" required @disabled(($providerRateState?->cooldownRemainingSeconds ?? 0) > 0)>
                    <button class="rounded border px-3 py-2 font-semibold" type="submit" @disabled(($providerRateState?->cooldownRemainingSeconds ?? 0) > 0)>Search</button>
                </form>
                @foreach(($musicBrainzReleaseGroupResults ?? []) as $group)
                    <div class="mt-3 rounded border p-3 text-sm">
                        <strong>{{ $group['title'] }}</strong>
                        <div class="text-xs text-slate-500">{{ $group['primary_type'] ?: '—' }} · {{ $group['first_release_date'] ?: 'date n/a' }}</div>
                        @if($group['disambiguation'])<div class="mt-1 text-xs text-slate-500">{{ $group['disambiguation'] }}</div>@endif
                        <div class="mt-2 font-mono text-xs">{{ $group['id'] }}</div>
                        @can('manage-providers')
                            <form class="mt-2" method="POST" action="{{ route('admin.providers.musicbrainz.releases.import', $provider) }}">
                                @csrf
                                <input type="hidden" name="entity_type" value="release_group">
                                <input type="hidden" name="mbid" value="{{ $group['id'] }}">
                                <button class="rounded border px-3 py-2 font-semibold" type="submit">Import Release Group</button>
                            </form>
                        @endcan
                    </div>
                @endforeach
            </div>

            <div class="rounded border p-4">
                <h3 class="font-semibold">Release search</h3>
                <p class="mt-1 text-sm text-slate-500">Specific edition/country/date/barcode release identity.</p>
                <form class="mt-4 flex gap-2" method="GET" action="{{ route('admin.providers.show', $provider) }}">
                    <input name="musicbrainz_release_query" value="{{ $musicBrainzReleaseQuery ?? '' }}" class="min-w-0 flex-1 rounded border p-2" placeholder="Random Access Memories…" required @disabled(($providerRateState?->cooldownRemainingSeconds ?? 0) > 0)>
                    <button class="rounded border px-3 py-2 font-semibold" type="submit" @disabled(($providerRateState?->cooldownRemainingSeconds ?? 0) > 0)>Search</button>
                </form>
                @foreach(($musicBrainzReleaseResults ?? []) as $release)
                    <div class="mt-3 rounded border p-3 text-sm">
                        <strong>{{ $release['title'] }}</strong>
                        <div class="text-xs text-slate-500">{{ $release['status'] ?: '—' }} · {{ $release['country'] ?: '—' }} · {{ $release['date'] ?: 'date n/a' }}</div>
                        @if($release['release_group_title'])<div class="mt-1 text-xs text-slate-500">Group: {{ $release['release_group_title'] }}</div>@endif
                        <div class="mt-2 font-mono text-xs">{{ $release['id'] }}</div>
                        @can('manage-providers')
                            <form class="mt-2" method="POST" action="{{ route('admin.providers.musicbrainz.releases.import', $provider) }}">
                                @csrf
                                <input type="hidden" name="entity_type" value="release">
                                <input type="hidden" name="mbid" value="{{ $release['id'] }}">
                                <button class="rounded border px-3 py-2 font-semibold" type="submit">Import Release</button>
                            </form>
                        @endcan
                    </div>
                @endforeach
            </div>
        </div>

        <div class="mt-6 rounded border p-4" data-musicbrainz-recording-workbench>
            <h3 class="font-semibold">Recording search</h3>
            <p class="mt-1 text-sm text-slate-500">Unique mix/edit identity with duration, Artist Credit and ISRC evidence. Import Recording after its Artists when possible.</p>
            <form class="mt-4 flex gap-2" method="GET" action="{{ route('admin.providers.show', $provider) }}">
                <input name="musicbrainz_recording_query" value="{{ $musicBrainzRecordingQuery ?? '' }}" class="min-w-0 flex-1 rounded border p-2" placeholder="Pink Venom, Get Lucky, Money…" required @disabled(($providerRateState?->cooldownRemainingSeconds ?? 0) > 0)>
                <button class="rounded border px-3 py-2 font-semibold" type="submit" @disabled(($providerRateState?->cooldownRemainingSeconds ?? 0) > 0)>Search</button>
            </form>
            @if(($musicBrainzRecordingResults ?? []) !== [])
                <div class="mt-4 overflow-x-auto">
                    <table class="w-full min-w-[900px] text-left text-sm">
                        <thead class="text-xs uppercase tracking-wide text-slate-500"><tr><th class="py-2 pr-3">Recording</th><th class="py-2 pr-3">Artist credit</th><th class="py-2 pr-3">Duration / ISRC</th><th class="py-2 pr-3">MBID</th><th class="py-2">Action</th></tr></thead>
                        <tbody class="divide-y">
                        @foreach($musicBrainzRecordingResults as $recording)
                            <tr>
                                <td class="py-3 pr-3"><strong>{{ $recording['title'] }}</strong>@if($recording['disambiguation'])<div class="text-xs text-slate-500">{{ $recording['disambiguation'] }}</div>@endif</td>
                                <td class="py-3 pr-3">{{ $recording['artist_credit'] ?: '—' }}</td>
                                <td class="py-3 pr-3">@if($recording['length_ms']){{ gmdate('i:s', (int) floor($recording['length_ms'] / 1000)) }}@else—@endif @if(($recording['isrcs'] ?? []) !== [])<div class="font-mono text-xs text-slate-500">{{ implode(', ', array_slice($recording['isrcs'], 0, 3)) }}</div>@endif</td>
                                <td class="py-3 pr-3 font-mono text-xs">{{ $recording['id'] }}</td>
                                <td class="py-3">
                                    @can('manage-providers')
                                        <form method="POST" action="{{ route('admin.providers.musicbrainz.recordings.import', $provider) }}">
                                            @csrf
                                            <input type="hidden" name="mbid" value="{{ $recording['id'] }}">
                                            <button class="rounded border px-3 py-2 font-semibold" type="submit">Import Recording</button>
                                        </form>
                                    @endcan
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
        <div class="mt-6 rounded border p-4" data-musicbrainz-work-workbench>
            <h3 class="font-semibold">Work search</h3>
            <p class="mt-1 text-sm text-slate-500">Composition/song identity. Recording → Work relationships can also materialize a minimal Work automatically; use this lane to inspect or enrich a Work directly.</p>
            <form class="mt-4 flex gap-2" method="GET" action="{{ route('admin.providers.show', $provider) }}">
                <input name="musicbrainz_work_query" value="{{ $musicBrainzWorkQuery ?? '' }}" class="min-w-0 flex-1 rounded border p-2" placeholder="Pink Venom, Get Lucky…" required @disabled(($providerRateState?->cooldownRemainingSeconds ?? 0) > 0)>
                <button class="rounded border px-3 py-2 font-semibold" type="submit" @disabled(($providerRateState?->cooldownRemainingSeconds ?? 0) > 0)>Search</button>
            </form>
            @foreach(($musicBrainzWorkResults ?? []) as $work)
                <div class="mt-3 rounded border p-3 text-sm">
                    <strong>{{ $work['title'] }}</strong>
                    <div class="text-xs text-slate-500">{{ $work['type'] ?: 'Work' }} · {{ $work['language'] ?: 'language n/a' }} @if(($work['iswcs'] ?? []) !== []) · {{ implode(', ', array_slice($work['iswcs'], 0, 2)) }} @endif</div>
                    @if($work['disambiguation'])<div class="mt-1 text-xs text-slate-500">{{ $work['disambiguation'] }}</div>@endif
                    <div class="mt-2 font-mono text-xs">{{ $work['id'] }}</div>
                    @can('manage-providers')
                        <form class="mt-2" method="POST" action="{{ route('admin.providers.musicbrainz.works.import', $provider) }}">
                            @csrf
                            <input type="hidden" name="mbid" value="{{ $work['id'] }}">
                            <button class="rounded border px-3 py-2 font-semibold" type="submit">Import Work</button>
                        </form>
                    @endcan
                </div>
            @endforeach
        </div>
    </div>
</x-ui.card>
@endif

<x-ui.card class="mt-6"><div class="p-5"><h2 class="font-semibold">Hoạt động gần đây</h2><p class="mt-1 text-sm text-slate-500">Các lần nhập, đồng bộ và thay đổi trạng thái gần nhất.</p><div class="mt-4 grid gap-4 lg:grid-cols-2"><div><h3 class="text-sm font-semibold">Tác vụ dữ liệu</h3>@forelse($importRuns->take(8) as $run)@php($runState=$presentation->importStatus($run->status))<a href="{{ route('admin.imports.show',$run) }}" class="mt-2 flex items-center justify-between gap-3 rounded border p-3 text-sm"><span><strong>{{ $presentation->operation($run->operation) }}</strong><small class="block text-slate-500">{{ $run->created_at?->format('d/m/Y H:i') }}</small></span><x-ui.badge :variant="$runState['tone']">{{ $runState['label'] }}</x-ui.badge></a>@empty<p class="mt-2 text-sm text-slate-500">Chưa có tác vụ dữ liệu.</p>@endforelse</div><div><h3 class="text-sm font-semibold">Thay đổi trạng thái</h3>@forelse($operationAudits->take(8) as $audit)<div class="mt-2 rounded border p-3 text-sm"><strong>{{ $presentation->providerAction($audit->action) }}</strong><small class="block text-slate-500">{{ $audit->occurred_at }}</small><p class="mt-1 text-slate-600">{{ $audit->rationale }}</p></div>@empty<p class="mt-2 text-sm text-slate-500">Chưa có thay đổi trạng thái.</p>@endforelse</div></div></div></x-ui.card>

<details class="mt-6 rounded-xl border border-slate-200 bg-white p-5" data-admin-technical-details><summary class="cursor-pointer font-semibold">Chi tiết kỹ thuật</summary><div class="mt-5"><h3 class="font-semibold">Capabilities</h3><div class="mt-3 grid gap-2 sm:grid-cols-2">@foreach($provider->capabilities as $capability)<div class="rounded border p-3 text-sm"><strong>{{ $capability->capability }}</strong><div class="text-xs text-slate-500">{{ $capability->status }} · TTL {{ $capability->cache_ttl_seconds ?? '—' }}</div></div>@endforeach</div><h3 class="mt-6 font-semibold">Sync runs</h3><div class="mt-3 space-y-2">@foreach($syncRuns as $sync)<div class="rounded border p-3 text-sm">{{ $sync->operation }} · {{ $sync->status }} · {{ $sync->processed_count }} processed · {{ $sync->failed_count }} failed</div>@endforeach</div></div></details>
@endsection

@if($provider->slug === 'youtube')
<x-ui.card class="mt-6" data-admin-youtube-workbench>
    <div class="p-5">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Video destination workbench</p>
                <h2 class="mt-1 text-lg font-semibold">YouTube Recording candidate discovery</h2>
                <p class="mt-1 max-w-3xl text-sm text-slate-500">Nhập canonical Recording ULID. SongChart dùng title + ordered Artist Credit để gọi search.list, sau đó xác minh candidate bằng videos.list. Chỉ thao tác Approve mới ghi provider destination.</p>
            </div>
            <div class="rounded border px-3 py-2 text-xs text-slate-500">search.list budget: {{ config('songchart.providers.youtube.quota.search_daily_limit') }}/UTC day</div>
        </div>

        <form class="mt-5 flex flex-col gap-3 sm:flex-row" method="GET" action="{{ route('admin.providers.show', $provider) }}">
            <input name="youtube_recording_id" value="{{ $youtubeRecordingId ?? '' }}" class="min-w-0 flex-1 rounded border p-3 font-mono text-sm" placeholder="Canonical Recording ULID" required>
            <button class="rounded border px-4 py-3 font-semibold" type="submit">Find YouTube candidates</button>
        </form>

        @if($youtubeError ?? null)
            <div class="mt-4 rounded border bg-amber-50 p-3 text-sm text-amber-950">{{ $youtubeError }}</div>
        @endif

        @if($youtubeRecording ?? null)
            <div class="mt-5 rounded border p-4 text-sm">
                <div class="text-xs uppercase tracking-wide text-slate-500">Canonical Recording</div>
                <strong>{{ $youtubeRecording->title }}</strong>
                <div class="mt-1 font-mono text-xs text-slate-500">{{ $youtubeRecording->getKey() }}</div>
            </div>
        @endif

        @if(($youtubeCandidates ?? []) !== [])
            <div class="mt-5 space-y-3">
                @foreach($youtubeCandidates as $candidate)
                    <article class="rounded border p-4" data-youtube-candidate="{{ $candidate->resourceId }}">
                        <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                            <div class="min-w-0 text-sm">
                                <div class="flex flex-wrap items-center gap-2">
                                    <strong>{{ $candidate->title }}</strong>
                                    <x-ui.badge :variant="$candidate->score >= 85 ? 'success' : ($candidate->score >= 65 ? 'warning' : 'neutral')">Score {{ $candidate->score }}</x-ui.badge>
                                    <x-ui.badge variant="neutral">{{ $candidate->decision }}</x-ui.badge>
                                </div>
                                <div class="mt-1 text-xs text-slate-500">{{ $candidate->channelTitle ?: 'Unknown channel' }} · {{ $candidate->durationMs !== null ? number_format($candidate->durationMs / 1000, 1).'s' : 'duration n/a' }} · {{ $candidate->embeddable ? 'embeddable' : 'not embeddable' }}</div>
                                <div class="mt-2 font-mono text-xs">{{ $candidate->resourceId }}</div>
                                <p class="mt-2 text-xs text-slate-500">Score là evidence hỗ trợ review, không phải xác nhận official channel. Stage 17.7 yêu cầu người quản trị approve.</p>
                            </div>
                            @can('manage-providers')
                                <form method="POST" action="{{ route('admin.providers.youtube.destinations.approve', $provider) }}">
                                    @csrf
                                    <input type="hidden" name="recording_id" value="{{ $youtubeRecording->getKey() }}">
                                    <input type="hidden" name="video_id" value="{{ $candidate->resourceId }}">
                                    <button class="rounded border px-3 py-2 font-semibold disabled:opacity-50" type="submit" @disabled(! $candidate->embeddable)>Verify again & approve</button>
                                </form>
                            @endcan
                        </div>
                    </article>
                @endforeach
            </div>
        @endif
    </div>
</x-ui.card>
@endif
