@props(['item'])
@php
    $variants=['artist'=>'artist','recording'=>'recording','release_group'=>'release','release'=>'release','version'=>'version','work'=>'work','collection'=>'collection'];
    $itemUrl = $item['url'] ?? \App\Support\Catalog\PublicEntityUrl::to(
        (string) $item['type'],
        (string) $item['slug'],
        isset($item['artist_type']) ? (string) $item['artist_type'] : null,
    );
@endphp
<article class="flex gap-3 border-b border-[var(--sc-border)] py-5 last:border-0 sm:gap-4">
    <div class="grid h-12 w-12 shrink-0 place-items-center rounded-[var(--sc-radius-control)] bg-[var(--sc-bg-subtle)] font-bold text-[var(--sc-primary)] sm:h-14 sm:w-14" aria-hidden="true">{{ mb_substr($item['title'],0,1) }}</div>
    <div class="min-w-0 flex-1">
        <div class="flex flex-wrap items-center gap-2"><x-ui.badge :variant="$variants[$item['type']]">{{ $item['label'] }}</x-ui.badge>@if($item['verified'])<x-ui.badge variant="success">Đã xác minh</x-ui.badge>@else<x-ui.badge variant="warning">Chưa xác minh</x-ui.badge>@endif</div>
        <h2 class="mt-2 text-lg font-bold leading-snug"><a class="rounded-sm hover:text-[var(--sc-primary)] focus:outline-none focus:ring-2 focus:ring-[var(--sc-focus)]" href="{{ $itemUrl }}">{{ $item['title'] }}</a></h2>
        <p class="mt-1 text-sm text-[var(--sc-text-secondary)]">{{ $item['context'] }}</p>
        @if(($item['description'] ?? '') !== '')
            <p class="mt-2 max-w-2xl text-sm leading-6 text-[var(--sc-text-secondary)]">{{ $item['description'] }}</p>
        @endif
        <p class="mt-2 text-xs font-medium text-[var(--sc-text-muted)]">{{ $item['meta'] }}</p>
        <a href="{{ $itemUrl }}" class="mt-3 inline-flex min-h-11 items-center font-semibold text-[var(--sc-primary)] sm:hidden">Xem chi tiết <span class="ml-2" aria-hidden="true">→</span></a>
    </div>
    <a href="{{ $itemUrl }}" class="hidden min-h-11 shrink-0 items-center self-center rounded-[var(--sc-radius-control)] px-2 font-semibold text-[var(--sc-primary)] hover:bg-[var(--sc-bg-subtle)] sm:inline-flex">Xem <span class="ml-1" aria-hidden="true">→</span></a>
</article>
