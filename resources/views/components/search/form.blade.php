@props(['query'=>'', 'type'=>'all', 'sort'=>'relevance', 'hero'=>false, 'inputId'=>'catalog-search'])
<form action="{{ route('search') }}" method="GET" role="search" class="space-y-4">
    <div class="flex flex-col gap-3 sm:flex-row">
        <div class="min-w-0 flex-1">
            <label for="{{ $inputId }}" class="sc-sr-only">Tìm nghệ sĩ, bài hát, album, phiên bản hoặc tác phẩm</label>
            <input id="{{ $inputId }}" name="q" value="{{ $query }}" maxlength="100" autocomplete="off"
                placeholder="Ví dụ: Radiohead, OK Computer, Creep..."
                class="min-h-12 w-full rounded-[var(--sc-radius-control)] border border-[var(--sc-border-strong)] bg-white px-4 text-base shadow-sm focus:border-[var(--sc-primary)] focus:outline-none focus:ring-4 focus:ring-[color-mix(in_srgb,var(--sc-focus)_18%,transparent)] {{ $hero ? 'md:min-h-14 md:text-lg' : '' }}">
        </div>
        <x-ui.button type="submit" size="lg">Tìm kiếm</x-ui.button>
    </div>
    <div class="flex flex-wrap gap-2" aria-label="Lọc theo loại thực thể">
        @foreach(['all'=>'Tất cả','artist'=>'Nghệ sĩ','recording'=>'Bài hát','release'=>'Album','version'=>'Phiên bản','work'=>'Tác phẩm','collection'=>'Bộ sưu tập'] as $key=>$label)
            <label class="cursor-pointer">
                <input type="radio" name="type" value="{{ $key }}" class="peer sr-only" @checked($type===$key) onchange="this.form.submit()">
                <span class="inline-flex min-h-11 items-center rounded-full border px-4 text-sm font-semibold transition peer-checked:border-[var(--sc-primary)] peer-checked:bg-[var(--sc-primary)] peer-checked:text-white">{{ $label }}</span>
            </label>
        @endforeach
    </div>
    @unless($hero)
    <div class="flex items-center gap-2">
        <label for="search-sort" class="text-sm font-semibold">Sắp xếp</label>
        <select id="search-sort" name="sort" onchange="this.form.submit()" class="min-h-11 rounded-[var(--sc-radius-control)] border border-[var(--sc-border-strong)] bg-white px-3">
            <option value="relevance" @selected($sort==='relevance')>Phù hợp nhất</option>
            <option value="title" @selected($sort==='title')>Tiêu đề A–Z</option>
            <option value="year_desc" @selected($sort==='year_desc')>Năm mới nhất</option>
        </select>
    </div>
    @endunless
</form>
