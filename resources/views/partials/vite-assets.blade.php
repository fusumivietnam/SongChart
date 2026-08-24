@php
    $viteReady ??= is_file(public_path('build/manifest.json')) || is_file(public_path('hot'));
@endphp

@if ($viteReady)
    @vite(['resources/css/app.css', 'resources/js/app.js'])
@elseif (app()->environment('local', 'testing'))
    <style>
        :root { font-family: Inter, ui-sans-serif, system-ui, sans-serif; color: #111827; background: #f8fafc; }
        * { box-sizing: border-box; }
        body { margin: 0; }
        .vite-build-warning {
            position: relative;
            z-index: 9999;
            padding: .75rem 1rem;
            border-bottom: 1px solid #f59e0b;
            background: #fff7ed;
            color: #9a3412;
            font-size: .875rem;
            line-height: 1.5;
            text-align: center;
        }
        .vite-build-warning code { font-weight: 700; }
    </style>
@else
    @php
        throw new RuntimeException(
            'Vite manifest is missing. Run npm ci (or npm install) and npm run build before serving the application.'
        );
    @endphp
@endif
