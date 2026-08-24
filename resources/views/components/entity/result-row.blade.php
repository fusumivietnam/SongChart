@props(['item'])
@php
    $variants=['artist'=>'artist','recording'=>'recording','release_group'=>'release','release'=>'release','version'=>'version','work'=>'work','collection'=>'collection'];
    $itemUrl = $item['url'] ?? \App\Support\Catalog\PublicEntityUrl::to(
        (string) $item['type'],
        (string) $item['slug'],
        isset($item['artist_type']) ? (string) $item['artist_type'] : null,
    );
@endphp
<article class="flex gap-4 border-b border-[var(--sc-border)] py-5 last:border-0">
    <div class="grid h-14 w-14 shrink-0 place-items-center rounded-[var(--sc-radius-control)] bg-[var(--sc-bg-subtle)] font-bold text-[var(--sc-primary)]" aria-hidden="true">{{ mb_substr($item['title'],0,1) }}</div>
    <div class="min-w-0 flex-1">
        <div class="flex flex-wrap items-center gap-2"><x-ui.badge :variant="$variants[$item['type']]">{{ $item['label'] }}</x-ui.badge>@if($item['verified'])<x-ui.badge variant="success">Đã xác minh</x-ui.badge>@else<x-ui.badge variant="warning">Chưa xác minh</x-ui.badge>@endif</div>
        <h2 class="mt-2 text-lg font-bold"><a class="hover:text-[var(--sc-primary)]" href="{{ $itemUrl }}">{{ $item['title'] }}</a></h2>
        <p class="mt-1 text-sm text-[var(--sc-text-secondary)]">{{ $item['context'] }}</p>
        <p class="mt-1 text-sm text-[var(--sc-text-muted)]">{{ $item['meta'] }}</p>
    </div>
    <a href="{{ $itemUrl }}" class="self-center font-semibold text-[var(--sc-primary)]">Xem <span aria-hidden="true">→</span></a>
</article>
