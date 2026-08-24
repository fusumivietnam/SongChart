@extends('layouts.admin')

@php($activeAdminNav = 'extensions')

@section('content')
<div class="mx-auto max-w-7xl px-4 py-8">
    <div class="flex items-end justify-between gap-4">
        <div><p class="text-secondary">Core / plugin / theme</p><h1 class="text-3xl font-bold">Extensions</h1></div>
    </div>
    @if(session('status'))<div class="mt-4 rounded-xl border p-4">{{ session('status') }}</div>@endif
    <section class="surface mt-8 rounded-2xl border p-6">
        <h2 class="text-xl font-semibold">Upload package</h2>
        <p class="text-secondary mt-1">Package is inspected before any code runs.</p>
        @can('manage-extensions')
        <form class="mt-4 flex flex-wrap gap-3" method="POST" action="{{ route('admin.extensions.upload') }}" enctype="multipart/form-data">
            @csrf
            <select name="type" class="rounded-xl border bg-transparent px-3 py-2"><option value="plugin">Plugin</option><option value="theme">Theme</option></select>
            <input type="file" name="package" accept=".zip" required class="rounded-xl border px-3 py-2">
            <button class="rounded-xl bg-brand-600 px-4 py-2 font-semibold text-white">Inspect package</button>
        </form>
        @endcan
    </section>
    <div class="mt-8 grid gap-4 md:grid-cols-2">
        @forelse($extensions as $extension)
            <a href="{{ route('admin.extensions.show', $extension) }}" class="surface rounded-2xl border p-5">
                <div class="flex justify-between gap-4"><div><p class="text-sm uppercase text-secondary">{{ $extension->type }}</p><h2 class="text-lg font-semibold">{{ $extension->name }}</h2><p class="text-secondary">{{ $extension->slug }}</p></div><span>{{ $extension->active_version ?? '—' }}</span></div>
                <p class="mt-4">{{ $extension->enabled ? 'Đang hoạt động' : 'Đã cài, chưa hoạt động' }}</p>
            </a>
        @empty
            <p class="text-secondary">Chưa có extension trong database registry.</p>
        @endforelse
    </div>
</div>
@endsection
