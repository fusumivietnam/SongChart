<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title>{{ $title ?? 'Quản trị' }} · {{ config('app.name') }}</title>
    <meta name="robots" content="noindex,nofollow">
    @include('partials.vite-assets')
</head>
<body class="admin-body" x-data="{ sidebarOpen: false }" @keydown.escape.window="sidebarOpen=false">
<a href="#admin-main" class="sr-only focus:not-sr-only focus:fixed focus:left-4 focus:top-4 focus:z-50 focus:rounded focus:bg-white focus:px-4 focus:py-3 focus:font-semibold focus:shadow">Bỏ qua điều hướng, đến nội dung chính</a>
@if (isset($viteReady) && ! $viteReady && app()->environment('local', 'testing'))
    <div class="vite-build-warning" role="status">Giao diện phát triển chưa được đồng bộ đầy đủ. Hãy chạy lại quy trình chuẩn bị môi trường trước khi đánh giá UI.</div>
@endif
<div class="admin-shell">
    <div x-show="sidebarOpen" x-cloak class="admin-sidebar-overlay lg:hidden" @click="sidebarOpen=false" aria-hidden="true"></div>
    <x-admin.sidebar :active="$activeAdminNav ?? 'dashboard'" />
    <div class="admin-workspace">
        <x-admin.topbar />
        <main id="admin-main" class="admin-main" tabindex="-1">{{ $slot ?? '' }}@yield('content')@if(isset($destinationAttention))@include('admin.operations._provider-destination-attention')@endif</main>
    </div>
</div>
</body>
</html>
