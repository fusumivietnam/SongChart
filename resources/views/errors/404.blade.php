@extends('layouts.frontend')
@php($activeNav = null)
@section('title', 'Không tìm thấy trang | '.config('app.name'))
@section('description', 'Trang bạn tìm không tồn tại hoặc đã được chuyển. Quay lại SongChart hoặc tìm trong canonical catalog.')
@push('head')
<meta name="robots" content="noindex,follow">
@endpush
@section('content')
<div class="sc-container py-12 md:py-20">
    <section class="mx-auto max-w-2xl text-center" aria-labelledby="not-found-title">
        <p class="text-sm font-semibold uppercase tracking-wide text-[var(--sc-primary)]">404 · Không tìm thấy</p>
        <h1 id="not-found-title" class="mt-3 text-4xl font-bold tracking-tight md:text-5xl">Trang này không còn ở đây</h1>
        <p class="mx-auto mt-4 max-w-xl text-base leading-7 text-[var(--sc-text-secondary)]">
            Đường dẫn có thể đã thay đổi, hoặc nội dung chưa có trong public catalog. Bạn có thể quay lại trang chủ hoặc tìm theo tên nghệ sĩ, bản phát hành, bản thu hay tác phẩm.
        </p>
        <div class="mt-8 flex flex-wrap justify-center gap-3">
            <x-ui.button :href="route('home')">Về trang chủ</x-ui.button>
            <x-ui.button variant="secondary" :href="route('search')">Tìm trong SongChart</x-ui.button>
        </div>
    </section>
</div>
@endsection
