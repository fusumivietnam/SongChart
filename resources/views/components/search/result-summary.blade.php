@props(['query', 'type' => 'all', 'result'])
@php
    $labels = ['all'=>'Tất cả thực thể','artist'=>'Nghệ sĩ','recording'=>'Bản thu','release'=>'Album','version'=>'Phiên bản','work'=>'Tác phẩm','collection'=>'Bộ sưu tập'];
@endphp
<div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
    <div>
        <p class="text-sm font-semibold text-[var(--sc-primary)]" aria-live="polite">
            @if($result['total'] > 0)
                Hiển thị {{ $result['from'] }}–{{ $result['to'] }} trong {{ $result['total'] }} kết quả
            @else
                0 kết quả
            @endif
        </p>
        <h1 id="search-results-title" class="sc-page-title mt-1">Kết quả cho “{{ $query }}”</h1>
        <p class="mt-2 text-sm text-[var(--sc-text-secondary)]">Bộ lọc: {{ $labels[$type] ?? $labels['all'] }}</p>
    </div>
    @if($type !== 'all')
        <x-ui.button variant="ghost" :href="route('search', ['q'=>$query, 'type'=>'all'])">Xóa bộ lọc</x-ui.button>
    @endif
</div>
