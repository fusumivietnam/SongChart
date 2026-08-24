@props(['active' => null])
@php
$items = [
    ['key' => 'discover', 'label' => 'Khám phá', 'icon' => 'home', 'href' => route('home')],
    ['key' => 'search', 'label' => 'Tìm kiếm', 'icon' => 'search', 'href' => route('search')],
    ['key' => 'artists', 'label' => 'Nghệ sĩ', 'icon' => 'artist', 'href' => url('/artists')],
    ['key' => 'collections', 'label' => 'Bộ sưu tập', 'icon' => 'collection', 'href' => url('/collections')],
    ['key' => 'account', 'label' => 'Tài khoản', 'icon' => 'user', 'href' => auth()->check() ? url('/account') : url('/login')],
];
@endphp
<nav class="sc-mobile-nav lg:hidden" aria-label="Điều hướng di động">
    @foreach($items as $item)
        <a href="{{ $item['href'] }}" @class(['sc-mobile-nav-item', 'is-active' => $active === $item['key']])>
            <x-icons.icon :name="$item['icon']" class="h-5 w-5" />
            <span>{{ $item['label'] }}</span>
        </a>
    @endforeach
</nav>
