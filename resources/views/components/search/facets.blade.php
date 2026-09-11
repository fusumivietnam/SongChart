@props(['query', 'activeType' => 'all', 'sort' => 'relevance', 'counts' => []])
@php
    $types = [
        'all' => 'Tất cả',
        'artist' => 'Nghệ sĩ',
        'recording' => 'Bản thu',
        'release' => 'Album',
        'version' => 'Phiên bản',
        'work' => 'Tác phẩm',
        'collection' => 'Bộ sưu tập',
    ];
@endphp
<nav aria-label="Lọc kết quả theo loại thực thể" class="flex gap-2 overflow-x-auto pb-1 lg:block lg:space-y-1 lg:overflow-visible lg:pb-0">
    @foreach($types as $value => $label)
        @php($isActive = $activeType === $value)
        <a
            href="{{ route('search', ['q' => $query, 'type' => $value, 'sort' => $sort]) }}"
            @class([
                'flex min-h-11 shrink-0 items-center justify-between gap-3 rounded-[var(--sc-radius-control)] px-3 py-2 text-sm font-semibold transition lg:w-full',
                'bg-[var(--sc-primary)] text-white' => $isActive,
                'border border-[var(--sc-border)] bg-white text-[var(--sc-text-secondary)] hover:bg-[var(--sc-bg-subtle)] hover:text-[var(--sc-text-primary)] lg:border-transparent lg:bg-transparent' => ! $isActive,
            ])
            @if($isActive) aria-current="page" @endif
        >
            <span>{{ $label }}</span>
            <span @class([
                'rounded-full px-2 py-0.5 text-xs',
                'bg-white/20 text-white' => $isActive,
                'bg-[var(--sc-bg-subtle)] text-[var(--sc-text-muted)]' => ! $isActive,
            ])>{{ $counts[$value] ?? 0 }}</span>
        </a>
    @endforeach
</nav>
