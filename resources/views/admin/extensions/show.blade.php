@extends('layouts.admin')

@php($activeAdminNav = 'extensions')

@section('content')
<div class="mx-auto max-w-7xl px-4 py-8">
    <a href="{{ route('admin.extensions.index') }}" class="text-secondary">← Extensions</a>
    <div class="mt-3 flex flex-wrap items-end justify-between gap-4"><div><p class="text-secondary">{{ $extension->type }}</p><h1 class="text-3xl font-bold">{{ $extension->name }}</h1><p>{{ $extension->slug }}</p></div><p>{{ $extension->enabled ? 'Đang hoạt động' : 'Đang tắt' }} · {{ $extension->active_version }}</p></div>
    @if(session('status'))<div class="mt-4 rounded-xl border p-4">{{ session('status') }}</div>@endif
    @if($errors->any())<div class="mt-4 rounded-xl border p-4">{{ $errors->first() }}</div>@endif
    <div class="mt-8 grid gap-6 lg:grid-cols-3">
        <section class="surface rounded-2xl border p-6 lg:col-span-2"><h2 class="text-xl font-semibold">Releases</h2>
            <div class="mt-4 space-y-3">
                @foreach($extension->releases as $release)
                    <div class="rounded-xl border p-4">
                        <div class="flex justify-between"><strong>{{ $release->version }}</strong><span>{{ $release->status }}</span></div>
                        <p class="text-secondary break-all text-sm">{{ $release->checksum_sha256 }}</p>
                        @if($release->version !== $extension->active_version)
                            @can('manage-extensions')
                                <form method="POST" action="{{ route('admin.extensions.rollback', $extension) }}" class="mt-3">@csrf<input type="hidden" name="version" value="{{ $release->version }}"><button class="font-semibold text-brand-600">Chuyển về phiên bản này</button></form>
                            @endcan
                        @endif
                    </div>
                @endforeach
            </div>
        </section>
        @can('manage-extensions')
            <aside class="space-y-6">
                <section class="surface rounded-2xl border p-6"><h2 class="font-semibold">Actions</h2>
                    @if($extension->type === 'plugin')<form method="POST" action="{{ route('admin.extensions.toggle', $extension) }}" class="mt-4">@csrf<button class="rounded-xl border px-4 py-2">{{ $extension->enabled ? 'Disable plugin' : 'Enable plugin' }}</button></form>@else<form method="POST" action="{{ route('admin.extensions.activate', $extension) }}" class="mt-4">@csrf<button class="rounded-xl border px-4 py-2">Activate theme</button></form>@endif
                    <form method="POST" action="{{ route('admin.extensions.upgrade', $extension) }}" enctype="multipart/form-data" class="mt-6">@csrf<label class="font-medium">Upgrade ZIP</label><input type="file" name="package" accept=".zip" required class="mt-2 w-full rounded-xl border p-2"><button class="mt-3 rounded-xl bg-brand-600 px-4 py-2 text-white">Inspect and upgrade</button></form>
                    <form method="POST" action="{{ route('admin.extensions.cleanup', $extension) }}" class="mt-6">@csrf<input type="number" name="keep" min="1" value="2" class="w-20 rounded-xl border bg-transparent p-2"><button class="ml-2 rounded-xl border px-3 py-2">Cleanup old releases</button></form>
                </section>
            </aside>
        @endcan
    </div>
    <section class="surface mt-6 rounded-2xl border p-6"><h2 class="text-xl font-semibold">Operation history</h2><div class="mt-4 space-y-2">@foreach($extension->operations as $operation)<div class="flex justify-between border-b py-3"><span>{{ $operation->type }} · {{ $operation->status }}</span><span class="text-secondary">{{ $operation->started_at }}</span></div>@endforeach</div></section>
</div>
@endsection
