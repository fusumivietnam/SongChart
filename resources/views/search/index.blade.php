@extends('layouts.frontend')
@php($activeNav='search')
@section('title', $query ? 'Tìm kiếm: '.$query.' | '.config('app.name') : 'Tìm kiếm | '.config('app.name'))
@section('description', 'Tìm nghệ sĩ, bản phát hành, bản thu, tác phẩm và bộ sưu tập trong canonical catalog SongChart.')
@push('head')
<link rel="canonical" href="{{ route('search') }}">
<meta name="robots" content="noindex,follow">
@endpush
@section('content')
<div class="sc-container py-8 md:py-12">
    <nav aria-label="Breadcrumb" class="text-sm text-[var(--sc-text-secondary)]"><a href="{{ route('home') }}">Trang chủ</a> <span aria-hidden="true">/</span> Tìm kiếm</nav>

    <header class="mt-6 max-w-3xl">
        <p class="sc-caption">CANONICAL DISCOVERY</p>
        <h1 class="sc-page-title mt-2">Tìm đúng thực thể âm nhạc</h1>
        <p class="mt-3 leading-7 text-[var(--sc-text-secondary)]">Tìm trong catalog SongChart trước, sau đó mở đúng identity, provenance và điểm đến provider đã được công bố.</p>
    </header>

    <div class="mt-6 rounded-[var(--sc-radius-panel)] border border-[var(--sc-border)] bg-[var(--sc-bg-surface)] p-4 shadow-sm md:p-5">
        <x-search.form :query="$query" :type="$type" :sort="$sort" :show-type-filters="false" />
    </div>

    @if($errors->any())
        <div class="mt-6"><x-ui.alert variant="danger" title="Không thể tìm kiếm">{{ $errors->first() }}</x-ui.alert></div>
    @endif

    @if($query==='')
        <div class="mt-10"><x-ui.empty-state title="Bắt đầu bằng một từ khóa" description="Tìm nghệ sĩ, bản thu, album, phiên bản, tác phẩm hoặc bộ sưu tập trong canonical catalog." /></div>
    @elseif($result['total_all']===0)
        <div class="mt-10"><x-ui.empty-state :title="'Không tìm thấy “'.$query.'”'" description="Hãy kiểm tra chính tả, dùng từ khóa rộng hơn hoặc thử một tên canonical khác.">
            <x-slot:actions><x-ui.button variant="secondary" :href="route('search',['q'=>$query,'type'=>'all'])">Tìm trong tất cả</x-ui.button><x-ui.button variant="ghost" href="mailto:content@songchart.test?subject=Missing%20content">Báo thiếu nội dung</x-ui.button></x-slot:actions>
        </x-ui.empty-state></div>
    @else
        <div class="mt-10 grid gap-8 lg:grid-cols-[15rem_minmax(0,1fr)] xl:grid-cols-[15rem_minmax(0,1fr)_18rem]">
            <aside class="min-w-0 lg:sticky lg:top-24 lg:self-start" aria-label="Bộ lọc tìm kiếm">
                <x-ui.card>
                    <h2 class="mb-3 font-bold">Loại thực thể</h2>
                    <x-search.facets :query="$query" :active-type="$type" :sort="$sort" :counts="$result['counts']" />
                </x-ui.card>
            </aside>

            <section id="search-results" aria-labelledby="search-results-title" class="min-w-0">
                <x-search.result-summary :query="$query" :type="$type" :result="$result" />

                @if($result['total']===0)
                    <div class="mt-5"><x-ui.empty-state title="Không có kết quả trong bộ lọc này" description="Từ khóa có kết quả ở loại thực thể khác. Hãy chọn Tất cả hoặc một loại có số lượng lớn hơn.">
                        <x-slot:actions><x-ui.button variant="secondary" :href="route('search',['q'=>$query,'type'=>'all','sort'=>$sort])">Xem tất cả {{ $result['total_all'] }} kết quả</x-ui.button></x-slot:actions>
                    </x-ui.empty-state></div>
                @else
                    <section aria-label="Danh sách kết quả" class="mt-5 rounded-[var(--sc-radius-card)] border border-[var(--sc-border)] bg-[var(--sc-bg-surface)] px-4 md:px-5">
                        @foreach($result['items'] as $item)<x-entity.result-row :item="$item" />@endforeach
                    </section>
                    <x-search.pagination :query="$query" :type="$type" :sort="$sort" :page="$result['page']" :last-page="$result['last_page']" />
                @endif
            </section>

            <aside class="space-y-5 lg:col-span-2 xl:col-span-1" aria-label="Ngữ cảnh tìm kiếm">
                @if($result['related'] !== [])
                    <x-ui.card><h2 class="font-bold">Tìm kiếm liên quan</h2><div class="mt-3 flex flex-wrap gap-2">@foreach($result['related'] as $related)<a class="inline-flex min-h-11 items-center rounded-full bg-[var(--sc-bg-subtle)] px-3 py-2 text-sm font-semibold hover:text-[var(--sc-primary)]" href="{{ route('search',['q'=>$related]) }}">{{ $related }}</a>@endforeach</div></x-ui.card>
                @endif
                <x-ui.card><h2 class="font-bold">Về kết quả</h2><p class="mt-3 text-sm leading-6 text-[var(--sc-text-secondary)]">Không có lượt nghe, chart hoặc độ phổ biến giả lập. Kết quả dùng canonical catalog nội bộ; public request không gọi provider API.</p><p class="mt-3 text-xs leading-5 text-[var(--sc-text-muted)]">Nhãn “chưa xác minh” cho biết metadata còn thiếu, không có nghĩa nội dung không tồn tại.</p></x-ui.card>
            </aside>
        </div>
    @endif
</div>
@endsection