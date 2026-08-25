<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="robots" content="noindex,nofollow">
    <title>{{ $title ?? 'UI Preview' }} · {{ config('app.name') }}</title>
    @include('partials.vite-assets')
</head>
<body class="ui-preview-body" x-data="{ navigationOpen: false }" @keydown.escape.window="navigationOpen = false">
    <a href="#preview-content" class="sc-sr-only focus:not-sr-only">Bỏ qua điều hướng</a>
    <div class="ui-preview-shell">
        <div x-show="navigationOpen" x-cloak class="ui-preview-overlay lg:hidden" @click="navigationOpen = false"></div>
        <aside class="ui-preview-sidebar" :class="navigationOpen && 'is-open'" aria-label="Điều hướng UI Preview">
            <div class="ui-preview-brand">
                <a href="{{ route('development.design-system.index') }}" class="sc-brand">
                    <span class="sc-brand-mark">SC</span>
                    <span>UI Preview</span>
                </a>
                <button type="button" class="ui-preview-close lg:hidden" @click="navigationOpen = false" aria-label="Đóng điều hướng"><x-icons.icon name="close" /></button>
            </div>
            <nav class="ui-preview-nav">
                @foreach($sections as $key => $meta)
                    <a href="{{ route('development.design-system.index', ['section' => $key]) }}" class="ui-preview-nav-item {{ $activeSection === $key ? 'is-active' : '' }}" @click="navigationOpen = false">
                        <span>{{ $meta['label'] }}</span>
                        <small>{{ $meta['description'] }}</small>
                    </a>
                @endforeach
            </nav>
            <div class="ui-preview-sidebar-footer">
                <a href="{{ route('home') }}">← Về frontend</a>
                <span>Local/staging only · no provider calls</span>
            </div>
        </aside>
        <div class="ui-preview-workspace">
            <header class="ui-preview-topbar">
                <button type="button" class="admin-topbar-icon lg:hidden" @click="navigationOpen = true" aria-label="Mở điều hướng"><x-icons.icon name="menu" /></button>
                <div>
                    <strong>{{ $activeMeta['label'] }}</strong>
                    <span>{{ $activeMeta['description'] }}</span>
                </div>
                <div class="ui-preview-viewport" aria-label="Breakpoint hiện tại"><span class="sm:hidden">XS</span><span class="hidden sm:inline md:hidden">SM</span><span class="hidden md:inline lg:hidden">MD</span><span class="hidden lg:inline xl:hidden">LG</span><span class="hidden xl:inline">XL</span></div>
            </header>
            <main id="preview-content" class="ui-preview-main">{{ $slot ?? '' }}@yield('content')</main>
        </div>
    </div>
</body>
</html>
