@extends('layouts.frontend')

@section('content')
<div class="sc-container py-8 md:py-12" data-public-catalog-index="{{ $entityType }}">
    <div class="max-w-3xl">
        <p class="text-sm font-semibold uppercase tracking-wide text-[var(--sc-text-secondary)]">Canonical catalog</p>
        <h1 class="mt-2 text-3xl font-bold">{{ $title }}</h1>
        <p class="mt-3 text-[var(--sc-text-secondary)]">{{ $description }}</p>
    </div>

    <form class="mt-6 flex max-w-2xl gap-3" method="GET">
        <input class="min-w-0 flex-1 rounded-xl border bg-white px-4 py-3" name="q" value="{{ $query }}" placeholder="Tìm trong {{ mb_strtolower($title) }}…">
        <button class="rounded-xl border px-4 py-3 font-semibold" type="submit">Tìm</button>
    </form>

    @if ($entities->count() === 0)
        <x-ui.card class="mt-8">
            <div class="p-6">
                <h2 class="font-semibold">Chưa có dữ liệu public phù hợp.</h2>
                <p class="mt-2 text-sm text-[var(--sc-text-secondary)]">
                    @if($entityType === 'release')
                        Chưa có Release canonical phù hợp. Hãy import Release Group/Release từ Admin → Providers → MusicBrainz rồi quay lại trang này.
                    @elseif($entityType === 'recording')
                        Chưa có Recording canonical phù hợp. Hãy import Recording từ Admin → Providers → MusicBrainz rồi quay lại trang này.
                    @elseif($entityType === 'collection')
                        Chưa có bộ sưu tập public. Collection private sẽ không được lộ ra frontend.
                    @else
                        Hãy import Artist từ Admin → Providers → MusicBrainz rồi quay lại trang này.
                    @endif
                </p>
            </div>
        </x-ui.card>
    @else
        <div class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @foreach($entities as $entity)
                @php($showUrl = \App\Support\Catalog\PublicEntityUrl::to($entityType, (string) $entity->slug))
                <a class="rounded-[var(--sc-radius-card)] border bg-[var(--sc-bg-surface)] p-5 transition hover:-translate-y-0.5" href="{{ $showUrl }}">
                    <p class="text-xs font-semibold uppercase tracking-wide text-[var(--sc-text-secondary)]">{{ $title }}</p>
                    <h2 class="mt-2 text-lg font-bold">{{ $entity->getAttribute($titleColumn) }}</h2>
                    <p class="mt-2 text-sm text-[var(--sc-text-secondary)]">
                        @if($entityType === 'artist')
                            {{ $entity->artist_type }}@if($entity->country_code) · {{ $entity->country_code }}@endif
                        @elseif($entityType === 'release')
                            {{ $entity->release_type }}@if($entity->released_on) · {{ $entity->released_on->format('Y') }}@endif
                        @elseif($entityType === 'recording')
                            @if($entity->duration_ms){{ gmdate('i:s', (int) floor($entity->duration_ms / 1000)) }}@else Duration n/a @endif
                        @else
                            {{ $entity->description ?: 'SongChart collection' }}
                        @endif
                    </p>
                </a>
            @endforeach
        </div>

        <div class="mt-8">{{ $entities->links() }}</div>
    @endif
</div>
@endsection
