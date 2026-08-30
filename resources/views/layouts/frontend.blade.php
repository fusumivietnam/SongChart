<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title>@hasSection('title')@yield('title')@else{{ $title ?? config('app.name') }}@endif</title>
    <meta name="description" content="@hasSection('description')@yield('description')@else{{ $description ?? 'Khám phá nghệ sĩ, bản phát hành, bản thu và nơi nghe hợp pháp.' }}@endif">
    @stack('head')
    @php
        $viteReady = is_file(public_path('build/manifest.json')) || is_file(public_path('hot'));
    @endphp
    @include('partials.vite-assets', ['viteReady' => $viteReady])
</head>
<body class="sc-frontend-body">
<a href="#main-content" class="sr-only focus:not-sr-only focus:fixed focus:left-4 focus:top-4 focus:z-50 focus:rounded-md focus:bg-white focus:px-4 focus:py-3 focus:font-semibold focus:text-[var(--sc-text-primary)] focus:shadow-lg">
    Bỏ qua điều hướng và đến nội dung chính
</a>
@if (isset($viteReady) && ! $viteReady && app()->environment('local', 'testing'))
    <div class="vite-build-warning" role="status">Vite assets chưa được build. Chạy <code>npm run build</code>.</div>
@endif
<x-shell.frontend-header :active="$activeNav ?? null" />
<main id="main-content" class="sc-frontend-main" tabindex="-1">{{ $slot ?? '' }}@yield('content')</main>
<x-shell.mobile-bottom-nav :active="$activeNav ?? null" />
@stack('scripts')
</body>
</html>
