@props(['active'])
@php
$items = [
    ['key' => 'overview', 'label' => 'Tổng quan', 'route' => 'account.overview'],
    ['key' => 'profile', 'label' => 'Hồ sơ', 'route' => 'account.profile'],
    ['key' => 'security', 'label' => 'Bảo mật', 'route' => 'account.security'],
];
@endphp
<nav aria-label="Điều hướng tài khoản" class="rounded-[var(--sc-radius-card)] border border-[var(--sc-border)] bg-white p-2 shadow-[var(--sc-shadow-card)]">
    @foreach($items as $item)
        <a href="{{ route($item['route']) }}" @class([
            'flex min-h-11 items-center rounded-[var(--sc-radius-control)] px-3 py-2 text-sm font-semibold',
            'bg-[var(--sc-primary)] text-white' => $active === $item['key'],
            'text-[var(--sc-text-secondary)] hover:bg-[var(--sc-bg-subtle)]' => $active !== $item['key'],
        ]) @if($active === $item['key']) aria-current="page" @endif>{{ $item['label'] }}</a>
    @endforeach
</nav>
