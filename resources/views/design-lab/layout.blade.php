<!DOCTYPE html>
<html lang="vi" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $concept['name'] ?? 'Frontend Design Lab' }} · SongChart</title>
    <meta name="robots" content="noindex,nofollow">
    @include('partials.vite-assets')
</head>
<body class="@yield('body-class', 'bg-slate-50 text-slate-950')">
@if (isset($viteReady) && ! $viteReady && app()->environment('local', 'testing'))
    <div class="vite-build-warning" role="status">
        Vite assets chưa được build. Chạy <code>scripts\build-assets-laragon.bat</code> tại thư mục gốc dự án.
    </div>
@endif

    @yield('content')
</body>
</html>
