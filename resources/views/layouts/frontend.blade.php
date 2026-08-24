<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title>{{ $title ?? config('app.name') }}</title>
    <meta name="description" content="{{ $description ?? 'Khám phá nghệ sĩ, bản phát hành, bản thu và nơi nghe hợp pháp.' }}">
    @stack('head')
    @php
        $viteReady = is_file(public_path('build/manifest.json')) || is_file(public_path('hot'));
    @endphp
    @include('partials.vite-assets', ['viteReady' => $viteReady])
</head>
<body class="sc-frontend-body">
@if (isset($viteReady) && ! $viteReady && app()->environment('local', 'testing'))
    <div class="vite-build-warning" role="status">Vite assets chưa được build. Chạy <code>scripts\build-assets-laragon.bat</code>.</div>
@endif
<x-shell.frontend-header :active="$activeNav ?? null" />
<main id="main-content" class="sc-frontend-main">{{ $slot ?? '' }}@yield('content')</main>
<x-shell.mobile-bottom-nav :active="$activeNav ?? null" />
@stack('scripts')
</body>
</html>
