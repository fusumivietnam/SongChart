@props(['active' => null])
@php
$items = [
    ['label' => 'Khám phá', 'route' => 'home', 'key' => 'discover'],
    ['label' => 'Nghệ sĩ', 'href' => url('/artists'), 'key' => 'artists'],
    ['label' => 'Phát hành', 'href' => url('/releases'), 'key' => 'releases'],
    ['label' => 'Bộ sưu tập', 'href' => url('/collections'), 'key' => 'collections'],
];
@endphp
<header class="sc-site-header">
    <div class="sc-container flex h-[var(--sc-header-height)] items-center gap-5">
        <a href="{{ route('home') }}" class="sc-brand" aria-label="SongChart — Trang chủ">
            <span class="sc-brand-mark" aria-hidden="true">SC</span>
            <span>SongChart</span>
        </a>

        <nav class="hidden items-center gap-1 lg:flex" aria-label="Điều hướng chính">
            @foreach($items as $item)
                <a href="{{ isset($item['route']) ? route($item['route']) : $item['href'] }}"
                   @class(['sc-nav-link', 'is-active' => $active === $item['key']])>
                    {{ $item['label'] }}
                </a>
            @endforeach
        </nav>

        <form class="ml-auto hidden min-w-0 max-w-md flex-1 md:block" action="{{ route('search') }}" method="get" role="search">
            <label class="sc-sr-only" for="header-search">Tìm kiếm toàn cục</label>
            <div class="sc-search-control">
                <x-icons.icon name="search" class="h-5 w-5" />
                <input id="header-search" name="q" value="{{ request('q') }}" placeholder="Tìm nghệ sĩ, bản thu, phát hành…" />
                <kbd>/</kbd>
            </div>
        </form>

        <div class="flex items-center gap-2">
            <a href="{{ route('search') }}" class="sc-header-icon md:hidden" aria-label="Tìm kiếm">
                <x-icons.icon name="search" />
            </a>
            @auth
                @can('access-admin')
                    <a href="{{ route('admin.dashboard') }}" class="hidden rounded-xl border px-3 py-2 text-sm font-semibold sm:inline-flex">Quản trị</a>
                @endcan
                <details class="relative">
                    <summary class="sc-avatar cursor-pointer list-none" aria-label="Mở menu tài khoản">{{ str(auth()->user()->name)->substr(0, 1)->upper() }}</summary>
                    <div class="absolute right-0 z-50 mt-2 w-64 rounded-[var(--sc-radius-card)] border border-[var(--sc-border)] bg-white p-2 shadow-[var(--sc-shadow-overlay)]">
                        <div class="px-3 py-2"><p class="truncate font-semibold">{{ auth()->user()->name }}</p><p class="truncate text-xs text-[var(--sc-text-muted)]">{{ auth()->user()->email }}</p></div>
                        <a href="{{ route('account.overview') }}" class="block rounded-[var(--sc-radius-control)] px-3 py-2 text-sm font-semibold hover:bg-[var(--sc-bg-subtle)]">Tài khoản</a>
                        <a href="{{ route('account.security') }}" class="block rounded-[var(--sc-radius-control)] px-3 py-2 text-sm font-semibold hover:bg-[var(--sc-bg-subtle)]">Bảo mật</a>
                        <form method="POST" action="{{ route('logout') }}">@csrf<button type="submit" class="w-full rounded-[var(--sc-radius-control)] px-3 py-2 text-left text-sm font-semibold hover:bg-[var(--sc-bg-subtle)]">Đăng xuất</button></form>
                    </div>
                </details>
            @else
                <a href="{{ route('login') }}" class="hidden rounded-xl border px-4 py-2 text-sm font-semibold sm:inline-flex">Đăng nhập</a>
                <a href="{{ route('login') }}" class="sc-avatar sm:hidden" aria-label="Đăng nhập"><x-icons.icon name="user" class="h-5 w-5" /></a>
            @endauth
        </div>
    </div>
</header>
