@extends('layouts.admin')

@php($activeAdminNav = 'extensions')

@section('content')
<div class="mx-auto max-w-3xl px-4 py-8">
    <h1 class="text-3xl font-bold">Package preflight</h1>
    <div class="surface mt-6 rounded-2xl border p-6">
        <dl class="grid gap-4 md:grid-cols-2"><div><dt class="text-secondary">Name</dt><dd class="font-semibold">{{ $result->manifest->name() }}</dd></div><div><dt class="text-secondary">Slug</dt><dd>{{ $result->manifest->slug() }}</dd></div><div><dt class="text-secondary">Version</dt><dd>{{ $result->manifest->version() }}</dd></div><div><dt class="text-secondary">SHA-256</dt><dd class="break-all text-sm">{{ $result->checksum }}</dd></div></dl>
        @foreach($result->warnings as $warning)<p class="mt-3 rounded-xl border p-3">Cảnh báo: {{ $warning }}</p>@endforeach
        @foreach($result->errors as $error)<p class="mt-3 rounded-xl border p-3">Không thể cài: {{ $error }}</p>@endforeach
        @if($result->passed())
        @can('manage-extensions')
        <form method="POST" action="{{ route('admin.extensions.install') }}" class="mt-6">
            @csrf<input type="hidden" name="stored" value="{{ $stored }}"><input type="hidden" name="type" value="{{ $result->manifest->type }}">
            <label class="flex gap-2"><input type="checkbox" name="enable" value="1"> Kích hoạt sau khi health check đạt</label>
            <button class="mt-4 rounded-xl bg-brand-600 px-4 py-2 font-semibold text-white">Install package</button>
        </form>
        @endcan
        @endif
    </div>
</div>
@endsection
