<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    @php
        $resolvedTitle = trim($__env->yieldContent('title')) ?: ($title ?? config('app.name'));
        $resolvedDescription = trim($__env->yieldContent('description')) ?: ($description ?? 'Khám phá nghệ sĩ, bản phát hành, bản thu và nơi nghe hợp pháp.');
        $resolvedCanonicalUrl = trim($__env->yieldContent('canonical_url')) ?: ($canonicalUrl ?? request()->url());
        $resolvedSocialTitle = trim($__env->yieldContent('social_title')) ?: ($socialTitle ?? $resolvedTitle);
        $resolvedSocialType = trim($__env->yieldContent('social_type')) ?: ($socialType ?? 'website');
        $resolvedSocialImage = trim($__env->yieldContent('social_image')) ?: ($socialImage ?? null);
        $resolvedSocialImageAlt = trim($__env->yieldContent('social_image_alt')) ?: ($socialImageAlt ?? null);
    @endphp
    <title>{{ $resolvedTitle }}</title>
    <meta name="description" content="{{ $resolvedDescription }}">
    @include('partials.social-meta', [
        'pageTitle' => $resolvedSocialTitle,
        'pageDescription' => $resolvedDescription,
        'canonicalUrl' => $resolvedCanonicalUrl,
        'socialType' => $resolvedSocialType,
        'socialImage' => $resolvedSocialImage,
        'socialImageAlt' => $resolvedSocialImageAlt,
    ])
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
<footer class="border-t border-[var(--sc-border-subtle)] bg-[var(--sc-bg-surface)]">
    <div class="sc-container flex flex-col gap-2 py-6 text-sm text-[var(--sc-text-secondary)] sm:flex-row sm:items-center sm:justify-between">
        <p>SongChart · canonical music information and governed listening destinations.</p>
        <a class="font-semibold text-[var(--sc-text-primary)] underline-offset-4 hover:underline" href="{{ route('privacy') }}">Privacy & data use</a>
    </div>
</footer>
<x-shell.mobile-bottom-nav :active="$activeNav ?? null" />
@stack('scripts')
</body>
</html>
