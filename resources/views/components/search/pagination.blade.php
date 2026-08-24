@props(['query', 'type', 'sort', 'page' => 1, 'lastPage' => 1])
@if($lastPage > 1)
<nav aria-label="Phân trang kết quả tìm kiếm" data-current-page="{{ $page }}" data-last-page="{{ $lastPage }}" class="mt-6 flex items-center justify-between gap-3 border-t border-[var(--sc-border)] pt-5">
    @if($page > 1)
        <x-ui.button variant="secondary" :href="route('search', ['q'=>$query, 'type'=>$type, 'sort'=>$sort, 'page'=>$page - 1])" rel="prev">← Trang trước</x-ui.button>
    @else
        <span></span>
    @endif

    <p class="text-sm text-[var(--sc-text-secondary)]" aria-live="polite">Trang <strong>{{ $page }}</strong> / {{ $lastPage }}</p>

    @if($page < $lastPage)
        <x-ui.button variant="secondary" :href="route('search', ['q'=>$query, 'type'=>$type, 'sort'=>$sort, 'page'=>$page + 1])" rel="next">Trang sau →</x-ui.button>
    @else
        <span></span>
    @endif
</nav>
@endif
