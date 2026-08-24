@extends('layouts.admin')
@section('content')
<x-admin.page-header :title="$title" :description="$description" />
<div class="mt-4"><a class="text-sm font-semibold hover:underline" href="{{ route('admin.catalog.entities.index', $entityType) }}">← Quay lại danh sách</a></div>

@if (session('status'))
    <div class="mt-4 rounded-xl border bg-[var(--sc-bg-surface)] p-4 text-sm font-semibold">{{ session('status') }}</div>
@endif

@if ($errors->any())
    <div class="mt-4 rounded-xl border p-4 text-sm">
        <p class="font-bold">Không thể lưu thay đổi.</p>
        <ul class="mt-2 list-disc space-y-1 pl-5">
            @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
        </ul>
    </div>
@endif

<div class="mt-6 grid gap-6 xl:grid-cols-[1.2fr_.8fr]" data-admin-section="catalog-entity-detail">
<div class="space-y-6">
@if ($entityType === 'artist')
    @can('manage-catalog')
    <x-ui.card>
        <div class="flex items-start justify-between gap-4">
            <div><h2 class="font-bold">Chỉnh sửa Artist canonical</h2><p class="mt-1 text-sm text-slate-500">Chỉ chỉnh dữ liệu canonical. External identifiers và raw provider evidence không bị ghi đè.</p></div>
        </div>
        <form class="mt-5 grid gap-4 sm:grid-cols-2" method="POST" action="{{ route('admin.catalog.artists.update', $entity->getKey()) }}">
            @csrf
            @method('PATCH')
            <label class="grid gap-1 text-sm"><span class="font-semibold">Tên</span><input class="rounded-lg border px-3 py-2" name="name" value="{{ old('name', $entity->name) }}" required></label>
            <label class="grid gap-1 text-sm"><span class="font-semibold">Sort name</span><input class="rounded-lg border px-3 py-2" name="sort_name" value="{{ old('sort_name', $entity->sort_name) }}"></label>
            <label class="grid gap-1 text-sm sm:col-span-2"><span class="font-semibold">Slug</span><input class="rounded-lg border px-3 py-2 font-mono" name="slug" value="{{ old('slug', $entity->slug) }}" pattern="[a-z0-9]+(?:-[a-z0-9]+)*" required><span class="text-xs text-slate-500">Lowercase letters, numbers và dấu gạch ngang. URL public đổi theo slug mới.</span></label>
            <label class="grid gap-1 text-sm"><span class="font-semibold">Artist type</span><input class="rounded-lg border px-3 py-2" name="artist_type" value="{{ old('artist_type', $entity->artist_type) }}" required></label>
            <label class="grid gap-1 text-sm"><span class="font-semibold">Country code</span><input class="rounded-lg border px-3 py-2 uppercase" maxlength="2" name="country_code" value="{{ old('country_code', $entity->country_code) }}"></label>
            <label class="grid gap-1 text-sm"><span class="font-semibold">Verification state</span><select class="rounded-lg border px-3 py-2" name="verification_state" required>@foreach(['unverified','candidate','verified','disputed','rejected'] as $state)<option value="{{ $state }}" @selected(old('verification_state', $entity->verification_state->value) === $state)>{{ $state }}</option>@endforeach</select></label>
            <label class="grid gap-1 text-sm sm:col-span-2"><span class="font-semibold">Lý do chỉnh sửa</span><textarea class="rounded-lg border px-3 py-2" name="rationale" rows="3" minlength="10" maxlength="2000" required>{{ old('rationale') }}</textarea><span class="text-xs text-slate-500">Bắt buộc để ghi privileged audit.</span></label>
            <div class="sm:col-span-2"><button class="rounded-lg border px-4 py-2 font-semibold" type="submit">Lưu Artist canonical</button></div>
        </form>
    </x-ui.card>
    @endcan
@endif

<x-ui.card><h2 class="font-bold">Thuộc tính canonical</h2><dl class="mt-4 grid gap-3 sm:grid-cols-2">
@foreach($entity->getAttributes() as $key => $value)<div><dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ $key }}</dt><dd class="mt-1 break-words">{{ is_bool($value) ? ($value ? 'true' : 'false') : ($value ?? '—') }}</dd></div>@endforeach
</dl></x-ui.card>
<x-ui.card><h2 class="font-bold">Quan hệ</h2><div class="mt-4 space-y-3">@forelse($relationships as $relationship)<div class="rounded-xl border p-3 text-sm"><strong>{{ $relationship->relationship_type }}</strong><p class="mt-1 text-slate-500">{{ $relationship->subject_type }}:{{ $relationship->subject_id }} → {{ $relationship->object_type }}:{{ $relationship->object_id }}</p></div>@empty<p class="text-sm text-slate-500">Chưa có quan hệ.</p>@endforelse</div></x-ui.card>
</div>
<div class="space-y-6">
<x-ui.card><h2 class="font-bold">External identifiers</h2><div class="mt-4 space-y-3">@forelse($identifiers as $identifier)<div class="rounded-xl border p-3"><div class="flex justify-between gap-3"><strong>{{ $identifier->namespace }}</strong>@if($identifier->is_primary)<span class="text-xs font-semibold">PRIMARY</span>@endif</div><p class="mt-1 break-all text-sm text-slate-600">{{ $identifier->value }}</p></div>@empty<p class="text-sm text-slate-500">Chưa có identifier.</p>@endforelse</div></x-ui.card>
<x-ui.card><h2 class="font-bold">Metadata conflicts</h2><div class="mt-4 space-y-3">@forelse($conflicts as $conflict)<div class="rounded-xl border p-3 text-sm"><strong>{{ $conflict->field_name }}</strong><p class="mt-1 text-slate-500">{{ $conflict->status }}</p></div>@empty<p class="text-sm text-slate-500">Không có conflict.</p>@endforelse</div></x-ui.card>
</div></div>
@endsection
