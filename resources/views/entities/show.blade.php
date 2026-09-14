@extends('layouts.frontend')
@section('title', $entity['title'].' · '.$entity['label'].' | '.config('app.name'))
@section('description', \Illuminate\Support\Str::limit(strip_tags((string) $entity['description']), 160, ''))
@section('canonical_url', url()->current())
@section('social_title', $entity['title'].' · '.$entity['label'])
@push('head')
@php
    $canonicalUrl = url()->current();
    $schemaType = match ($entity['type']) {
        'artist' => \App\Support\Catalog\PublicEntityUrl::isGroupArtistType((string) ($entity['artist_type'] ?? '')) ? 'MusicGroup' : 'Person',
        'release', 'release_group' => 'MusicAlbum',
        'recording' => 'MusicRecording',
        'work' => 'MusicComposition',
        default => 'CreativeWork',
    };
    $seoDescription = \Illuminate\Support\Str::limit(strip_tags((string) $entity['description']), 160, '');
    $structuredData = [
        '@context' => 'https://schema.org',
        '@type' => $schemaType,
        '@id' => $canonicalUrl.'#entity',
        'url' => $canonicalUrl,
        'name' => $entity['title'],
        'description' => $seoDescription,
    ];
@endphp
<meta name="robots" content="index,follow,max-image-preview:large">
<script type="application/ld+json">{!! json_encode($structuredData, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}</script>
@endpush
@section('content')
<div class="sc-container py-8 md:py-12" data-entity-type="{{ $entity['type'] }}" data-entity-slug="{{ $entity['slug'] }}">
    <nav aria-label="Breadcrumb" class="text-sm text-[var(--sc-text-secondary)]">
        <a href="{{ route('home') }}">Trang chủ</a>
        <span aria-hidden="true">/</span>
        <a href="{{ route('search',['type'=>$entity['type'],'q'=>$entity['title']]) }}">{{ $entity['label'] }}</a>
        <span aria-hidden="true">/</span>
        <span aria-current="page">{{ $entity['title'] }}</span>
    </nav>

    <header class="mt-7 rounded-[var(--sc-radius-card)] border border-[var(--sc-border)] bg-white p-5 shadow-[var(--sc-shadow-card)] md:p-7">
        <div class="flex flex-col gap-6 sm:flex-row sm:items-start">
            <div class="grid h-28 w-28 shrink-0 place-items-center rounded-[var(--sc-radius-card)] bg-[var(--sc-primary-soft)] text-4xl font-bold text-[var(--sc-primary)]" aria-hidden="true">{{ mb_substr($entity['title'],0,1) }}</div>
            <div class="min-w-0 flex-1">
                <p class="sc-caption">{{ $entity['eyebrow'] }}</p>
                <div class="mt-2 flex flex-wrap gap-2">
                    <x-ui.badge :variant="$entity['type']">{{ $entity['label'] }}</x-ui.badge>
                    @if($entity['verified'])
                        <x-ui.badge variant="success">Canonical đã xác minh</x-ui.badge>
                    @else
                        <x-ui.badge variant="warning">Cần bổ sung xác minh</x-ui.badge>
                    @endif
                </div>
                <h1 id="entity-title" class="mt-3 break-words text-4xl font-bold tracking-tight md:text-5xl">{{ $entity['title'] }}</h1>
                <p class="mt-3 text-lg leading-7 text-[var(--sc-text-secondary)]">{{ $entity['context'] }}</p>
                <p class="mt-2 text-sm text-[var(--sc-text-muted)]">{{ $entity['meta'] }}</p>
            </div>
        </div>
    </header>

    <nav aria-label="Nội dung thực thể" class="mt-4 flex gap-2 overflow-x-auto pb-1 text-sm">
        <a class="min-h-11 shrink-0 rounded-full border border-[var(--sc-border)] bg-white px-4 py-2.5 font-semibold" href="#overview">Tổng quan</a>
        <a class="min-h-11 shrink-0 rounded-full border border-[var(--sc-border)] bg-white px-4 py-2.5 font-semibold" href="#relationships">Quan hệ</a>
        <a class="min-h-11 shrink-0 rounded-full border border-[var(--sc-border)] bg-white px-4 py-2.5 font-semibold" href="#evidence">Nguồn & bằng chứng</a>
        <a class="min-h-11 shrink-0 rounded-full border border-[var(--sc-border)] bg-white px-4 py-2.5 font-semibold lg:hidden" href="#provider-destinations">Nơi nghe / xem</a>
    </nav>

    <div class="mt-8 grid gap-8 lg:grid-cols-[minmax(0,1fr)_22rem]">
        <article class="min-w-0 space-y-10" aria-labelledby="entity-title">
            @if($entity['type'] === 'recording')
                <x-provider.media-player :media="$media ?? null" />
            @endif

            <section id="overview" aria-labelledby="entity-overview-title" class="scroll-mt-24">
                <p class="sc-caption">CANONICAL OVERVIEW</p>
                <h2 id="entity-overview-title" class="sc-section-title mt-1">Tổng quan</h2>
                <p class="mt-3 max-w-3xl leading-7 text-[var(--sc-text-secondary)]">{{ $entity['description'] }}</p>
                <div class="mt-5"><x-entity.facts :facts="$entity['facts']" /></div>
            </section>

            <div id="relationships" class="scroll-mt-24">
                <x-entity.relationships :title="$entity['relationship_title']" :items="$entity['relationships']" />
            </div>

            <div id="evidence" class="scroll-mt-24">
                <x-entity.evidence-summary :entity="$entity" />
            </div>

            <section aria-labelledby="entity-provenance-title">
                <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <p class="sc-caption">SOURCE HISTORY</p>
                        <h2 id="entity-provenance-title" class="sc-section-title mt-1">Nguồn và provenance</h2>
                    </div>
                    <p class="text-sm text-[var(--sc-text-secondary)]">Bằng chứng hỗ trợ canonical identity, không thay thế identity.</p>
                </div>
                <div class="mt-4 overflow-x-auto rounded-[var(--sc-radius-card)] border border-[var(--sc-border)] bg-white" tabindex="0" aria-label="Bảng nguồn và provenance có thể cuộn ngang">
                    <table class="min-w-[36rem] w-full text-left text-sm">
                        <thead class="bg-[var(--sc-bg-subtle)]"><tr><th class="px-4 py-3">Nguồn</th><th class="px-4 py-3">Trạng thái</th><th class="px-4 py-3">Kiểm tra</th></tr></thead>
                        <tbody>
                            @forelse($entity['sources'] as $source)
                                <tr class="border-t border-[var(--sc-border)]"><td class="px-4 py-3 font-semibold">{{ $source['name'] }}</td><td class="px-4 py-3">{{ $source['status'] }}</td><td class="px-4 py-3">{{ $source['checked_at'] }}</td></tr>
                            @empty
                                <tr class="border-t border-[var(--sc-border)]"><td colspan="3" class="px-4 py-5 text-[var(--sc-text-secondary)]">Chưa có provenance public đủ điều kiện hiển thị.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
        </article>

        <aside class="min-w-0 space-y-5 lg:sticky lg:top-24 lg:self-start">
            <div id="provider-destinations" class="scroll-mt-24"><x-provider.chooser :entity="$entity" /></div>
            <x-ui.card>
                <p class="sc-caption">CANONICAL IDENTIFIERS</p>
                <div class="mt-2"><x-entity.identifiers :identifiers="$entity['identifiers']" /></div>
            </x-ui.card>
        </aside>
    </div>
</div>
@endsection
