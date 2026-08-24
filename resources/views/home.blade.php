@extends('layouts.frontend')
@php($activeNav = 'discover')
@section('content')
<section id="search" class="sc-home-hero">
    <div class="sc-container py-12 md:py-20">
        <div class="mx-auto max-w-4xl text-center">
            <p class="mb-3 font-semibold text-[var(--sc-primary)]">Music discovery & canonical metadata</p>
            <h1 class="text-4xl font-bold tracking-tight md:text-6xl">Bạn muốn tìm gì hôm nay?</h1>
            <p class="mx-auto mt-5 max-w-2xl text-lg text-[var(--sc-text-secondary)]">Tìm đúng nghệ sĩ, bản thu, phát hành, phiên bản hoặc tác phẩm; sau đó mở điểm đến chính thức trên provider phù hợp.</p>
        </div>

        <div class="mx-auto mt-9 max-w-4xl rounded-[var(--sc-radius-panel)] border border-[var(--sc-border)] bg-white p-5 shadow-[var(--sc-shadow-float)] md:p-7">
            <x-search.form hero />
            <div class="mt-4 flex flex-wrap items-center gap-2 text-sm text-[var(--sc-text-secondary)]">
                <span class="font-semibold">Thử tìm:</span>
                @foreach($examples as $example)
                    <a class="rounded-full bg-[var(--sc-bg-subtle)] px-3 py-2 font-semibold hover:text-[var(--sc-primary)]" href="{{ route('search', ['q'=>$example]) }}">{{ $example }}</a>
                @endforeach
            </div>
        </div>

        <div class="mx-auto mt-8 grid max-w-4xl gap-4 md:grid-cols-3">
            @foreach([
                ['Phân biệt thực thể','Biết rõ nghệ sĩ, bản thu, album, phiên bản và tác phẩm.'],
                ['Dữ liệu có nguồn','Canonical metadata giữ trạng thái xác minh và provenance.'],
                ['Mở đúng nơi nghe','SongChart điều hướng; không lưu trữ hoặc thay thế provider.']
            ] as [$title,$body])
                <article class="sc-card p-5"><h2 class="font-bold">{{ $title }}</h2><p class="mt-2 text-sm leading-6 text-[var(--sc-text-secondary)]">{{ $body }}</p></article>
            @endforeach
        </div>
    </div>
</section>

<section class="sc-home-section" aria-labelledby="entity-entry-title">
    <div class="sc-container">
        <div class="sc-home-section-heading">
            <div><p class="sc-caption">ĐI ĐÚNG LOẠI DỮ LIỆU</p><h2 id="entity-entry-title" class="sc-section-title mt-2">Khám phá theo thực thể</h2></div>
            <p>Mỗi lối vào giữ rõ loại thực thể để tránh nhầm bài hát, bản thu, album và tác phẩm.</p>
        </div>
        <div class="mt-7 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @foreach($entity_entries as $entry)<x-home.entity-entry :entry="$entry" />@endforeach
        </div>
    </div>
</section>

<section class="sc-home-section bg-[var(--sc-bg-subtle)]" aria-labelledby="featured-title">
    <div class="sc-container">
        <div class="sc-home-section-heading">
            <div><p class="sc-caption">CANONICAL STARTING POINTS</p><h2 id="featured-title" class="sc-section-title mt-2">Bắt đầu từ dữ liệu đã định danh</h2></div>
            <p>Các mục mẫu minh họa cách SongChart nối identity, metadata và đường dẫn chi tiết mà không tạo popularity giả.</p>
        </div>
        <div class="mt-7 grid gap-5 md:grid-cols-3">
            @foreach($featured as $item)<x-home.featured-card :item="$item" />@endforeach
        </div>
    </div>
</section>

<section class="sc-home-section" aria-labelledby="editorial-title">
    <div class="sc-container grid gap-6 lg:grid-cols-[minmax(0,1.25fr)_minmax(18rem,.75fr)]">
        <article class="rounded-[var(--sc-radius-panel)] border border-[var(--sc-primary-border)] bg-[var(--sc-primary-soft)] p-6 md:p-8">
            <x-ui.badge variant="entity">{{ $editorial['label'] }}</x-ui.badge>
            <h2 id="editorial-title" class="mt-4 text-3xl font-bold tracking-tight">{{ $editorial['title'] }}</h2>
            <p class="mt-3 max-w-2xl leading-7 text-[var(--sc-text-secondary)]">{{ $editorial['description'] }}</p>
            <div class="mt-5 flex flex-wrap gap-2">
                @foreach($editorial['items'] as $item)<span class="rounded-full border border-[var(--sc-primary-border)] bg-white px-3 py-2 text-sm font-semibold">{{ $item }}</span>@endforeach
            </div>
            <a href="{{ $editorial['url'] }}" class="mt-6 inline-flex min-h-11 items-center font-semibold text-[var(--sc-primary)]">Mở bộ sưu tập <span class="ml-2" aria-hidden="true">→</span></a>
            <p class="mt-5 text-xs text-[var(--sc-text-muted)]">Nguồn: {{ $editorial['provenance'] }}</p>
        </article>
        <aside class="sc-card p-6">
            <p class="sc-caption">SONGCHART KHÔNG PHẢI PLAYER</p>
            <h2 class="mt-2 text-2xl font-bold">Tìm hiểu ở đây, nghe tại điểm đến chính thức</h2>
            <ol class="mt-5 space-y-4 text-sm leading-6 text-[var(--sc-text-secondary)]">
                <li><strong class="text-[var(--sc-text-primary)]">1. Tìm đúng identity.</strong> Phân biệt rõ từng loại thực thể.</li>
                <li><strong class="text-[var(--sc-text-primary)]">2. Kiểm tra metadata.</strong> Xem trạng thái xác minh và provenance.</li>
                <li><strong class="text-[var(--sc-text-primary)]">3. Chọn provider.</strong> Rời SongChart qua liên kết được công bố rõ.</li>
            </ol>
        </aside>
    </div>
</section>
@endsection
