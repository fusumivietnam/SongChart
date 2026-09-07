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
@if (isset($viteReady) && ! $viteReady && app()->environment('local', 'testing'))
    <div class="vite-build-warning" role="status">Giao diện phát triển chưa được đồng bộ đầy đủ. Hãy chạy lại quy trình chuẩn bị môi trường trước khi đánh giá UI.</div>
@endif
<div class="admin-shell">
    <div x-show="sidebarOpen" x-cloak class="admin-sidebar-overlay lg:hidden" @click="sidebarOpen=false"></div>
    <x-admin.sidebar :active="$activeAdminNav ?? 'dashboard'" />
    <div class="admin-workspace">
        <x-admin.topbar />
        <main id="admin-main" class="admin-main">{{ $slot ?? '' }}@yield('content')@if(isset($destinationAttention))@include('admin.operations._provider-destination-attention')@endif</main>
    </div>
</div>
</body>
</html>
