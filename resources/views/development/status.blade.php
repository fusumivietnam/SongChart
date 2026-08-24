@extends('layouts.frontend')

@section('content')
<div class="sc-container py-8 md:py-12" data-development-status>
    <p class="text-sm font-semibold uppercase tracking-wide text-[var(--sc-text-secondary)]">Local development</p>
    <h1 class="mt-2 text-3xl font-bold">SongChart readiness</h1>
    <p class="mt-3 max-w-2xl text-[var(--sc-text-secondary)]">Trang này chỉ được đăng ký trong môi trường local hoặc testing.</p>

    <div class="mt-8 grid gap-3">
        @foreach ($checks as $name => $check)
            <article class="rounded-[var(--sc-radius-card)] border bg-[var(--sc-bg-surface)] p-4" data-check="{{ $name }}">
                <div class="flex items-center justify-between gap-4">
                    <h2 class="font-semibold">{{ str($name)->replace('_', ' ')->title() }}</h2>
                    <span class="rounded-full px-3 py-1 text-xs font-bold {{ $check['ok'] ? 'bg-green-100 text-green-800' : 'bg-amber-100 text-amber-900' }}">
                        {{ $check['ok'] ? 'OK' : 'ACTION' }}
                    </span>
                </div>
                <p class="mt-2 break-words text-sm text-[var(--sc-text-secondary)]">{{ $check['detail'] }}</p>
            </article>
        @endforeach
    </div>


    <h2 class="mt-10 text-xl font-bold">Provider readiness</h2>
    <div class="mt-4 grid gap-3">
        @foreach ($providers as $name => $provider)
            <article class="rounded-[var(--sc-radius-card)] border bg-[var(--sc-bg-surface)] p-4">
                <div class="flex items-center justify-between gap-4">
                    <h3 class="font-semibold">{{ $name }}</h3>
                    <span class="rounded-full px-3 py-1 text-xs font-bold">{{ $provider['status'] }}</span>
                </div>
                <p class="mt-2 text-sm text-[var(--sc-text-secondary)]">{{ $provider['detail'] }}</p>
            </article>
        @endforeach
    </div>

    <section class="mt-10 rounded-[var(--sc-radius-card)] border bg-[var(--sc-bg-surface)] p-5" data-musicbrainz-workbench>
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-[var(--sc-text-secondary)]">Stage 17.2 live provider workbench</p>
                <h2 class="mt-1 text-xl font-bold">MusicBrainz artist search and import</h2>
                <p class="mt-2 max-w-3xl text-sm text-[var(--sc-text-secondary)]">Search calls MusicBrainz live. Import queues the selected MBID through the immutable raw ledger, normalization, identity resolution and canonical mutation pipeline.</p>
            </div>
            <a class="rounded-xl border px-4 py-2 text-sm font-semibold" href="{{ route('admin.providers.index') }}">Provider registry</a>
        </div>

        @if (session('development_notice'))
            <div class="mt-4 rounded-xl border bg-green-50 px-4 py-3 text-sm text-green-900">{{ session('development_notice') }}</div>
        @endif

        <form class="mt-5 flex flex-col gap-3 sm:flex-row" method="GET" action="{{ route('development.status') }}">
            <label class="sr-only" for="musicbrainz-query">Artist name</label>
            <input id="musicbrainz-query" name="musicbrainz_query" value="{{ $musicBrainzQuery }}" class="min-w-0 flex-1 rounded-xl border bg-white px-4 py-3" placeholder="Daft Punk, Taylor Swift, Sơn Tùng M-TP…" required>
            <button class="rounded-xl border bg-[var(--sc-text-primary)] px-5 py-3 font-semibold text-white" type="submit">Search live</button>
        </form>

        @if ($musicBrainzError)
            <div class="mt-4 rounded-xl border bg-amber-50 px-4 py-3 text-sm text-amber-950">{{ $musicBrainzError }}</div>
        @endif

        @if ($musicBrainzResults !== [])
            <div class="mt-5 overflow-x-auto">
                <table class="w-full min-w-[760px] text-left text-sm">
                    <thead class="text-xs uppercase tracking-wide text-[var(--sc-text-secondary)]">
                        <tr><th class="py-2 pr-3">Artist</th><th class="py-2 pr-3">Country/type</th><th class="py-2 pr-3">MBID</th><th class="py-2">Action</th></tr>
                    </thead>
                    <tbody class="divide-y">
                    @foreach ($musicBrainzResults as $artist)
                        <tr>
                            <td class="py-3 pr-3"><strong>{{ $artist['name'] }}</strong>@if ($artist['disambiguation'])<div class="text-xs text-[var(--sc-text-secondary)]">{{ $artist['disambiguation'] }}</div>@endif</td>
                            <td class="py-3 pr-3">{{ $artist['country'] ?: '—' }} · {{ $artist['type'] ?: '—' }}</td>
                            <td class="py-3 pr-3 font-mono text-xs">{{ $artist['id'] }}</td>
                            <td class="py-3">
                                <form method="POST" action="{{ route('development.musicbrainz.artists.import') }}">
                                    @csrf
                                    <input type="hidden" name="mbid" value="{{ $artist['id'] }}">
                                    <button class="rounded-lg border px-3 py-2 font-semibold" type="submit">Import artist</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>

    <h2 class="mt-10 text-xl font-bold">Pipeline snapshot</h2>
    <div class="mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
        @foreach ($pipeline as $name => $value)
            <article class="rounded-[var(--sc-radius-card)] border bg-[var(--sc-bg-surface)] p-4">
                <p class="text-xs font-semibold uppercase tracking-wide text-[var(--sc-text-secondary)]">{{ str($name)->replace('_', ' ') }}</p>
                <p class="mt-2 text-2xl font-bold">{{ $value }}</p>
            </article>
        @endforeach
    </div>

    <h2 class="mt-10 text-xl font-bold">Important URLs</h2>
    <div class="mt-4 grid gap-2 sm:grid-cols-2">
        @foreach ($urls as $label => $url)
            <a class="rounded-xl border bg-white px-4 py-3 font-semibold" href="{{ $url }}">{{ $label }} <span class="block text-xs font-normal text-[var(--sc-text-secondary)]">{{ $url }}</span></a>
        @endforeach
    </div>
</div>
@endsection
