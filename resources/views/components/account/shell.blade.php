@props(['active', 'title', 'description' => null])
<div class="sc-container py-8 md:py-12" data-account-section="{{ $active }}">
    <nav aria-label="Breadcrumb" class="text-sm text-[var(--sc-text-secondary)]"><a href="{{ route('home') }}">Trang chủ</a> <span aria-hidden="true">/</span> Tài khoản</nav>
    <header class="mt-6">
        <p class="text-sm font-semibold text-[var(--sc-primary)]">Tài khoản SongChart</p>
        <h1 class="sc-page-title mt-1">{{ $title }}</h1>
        @if($description)<p class="mt-2 max-w-2xl text-[var(--sc-text-secondary)]">{{ $description }}</p>@endif
    </header>
    <div class="mt-8 grid gap-6 lg:grid-cols-[15rem_minmax(0,1fr)]">
        <aside><x-account.nav :active="$active" /></aside>
        <section class="min-w-0">{{ $slot }}</section>
    </div>
</div>
