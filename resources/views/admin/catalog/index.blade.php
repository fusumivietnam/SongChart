@extends('layouts.admin')
@section('content')
<x-admin.page-header :title="$title" :description="$description" />
<nav class="mt-5 flex flex-wrap gap-2" aria-label="Loại catalog">
@foreach($definitions as $key => $item)
<a href="{{ route('admin.catalog.entities.index', $key) }}" @class(['rounded-full border px-4 py-2 text-sm font-semibold', 'bg-slate-900 text-white' => $key === $entityType])>{{ $item['plural'] }}</a>
@endforeach
</nav>
<form method="get" class="mt-6 grid gap-3 rounded-2xl border bg-white p-4 md:grid-cols-[1fr_220px_180px_auto]" data-admin-section="catalog-administration">
<input name="q" value="{{ $filters['term'] }}" maxlength="100" placeholder="Tên hoặc slug" class="min-h-11 rounded-xl border px-3">
<select name="state" class="min-h-11 rounded-xl border px-3"><option value="">Mọi trạng thái</option>@foreach(['unverified'=>'Unverified','candidate'=>'Candidate','verified'=>'Verified','disputed'=>'Disputed','rejected'=>'Rejected'] as $value=>$label)<option value="{{ $value }}" @selected($filters['state']===$value)>{{ $label }}</option>@endforeach</select>
<select name="sort" class="min-h-11 rounded-xl border px-3"><option value="title" @selected($filters['sort']==='title')>Tên A–Z</option><option value="newest" @selected($filters['sort']==='newest')>Mới nhất</option><option value="oldest" @selected($filters['sort']==='oldest')>Cũ nhất</option></select>
<button class="min-h-11 rounded-xl bg-slate-900 px-5 font-semibold text-white">Áp dụng</button>
</form>
<div class="mt-6 overflow-hidden rounded-2xl border bg-white">
<table class="w-full text-left text-sm"><thead class="bg-slate-50"><tr><th class="px-4 py-3">Tên</th><th class="px-4 py-3">Slug</th><th class="px-4 py-3">Trạng thái</th><th class="px-4 py-3">Cập nhật</th></tr></thead><tbody>
@forelse($entities as $entity)
<tr class="border-t"><td class="px-4 py-3 font-semibold"><a class="hover:underline" href="{{ route('admin.catalog.entities.show', [$entityType, $entity->getKey()]) }}">{{ $entity->getAttribute($definition['title']) }}</a></td><td class="px-4 py-3 text-slate-500">{{ $entity->slug }}</td><td class="px-4 py-3">{{ $entity->verification_state->value ?? $entity->verification_state }}</td><td class="px-4 py-3 text-slate-500">{{ $entity->updated_at?->format('Y-m-d H:i') }}</td></tr>
@empty<tr><td colspan="4" class="px-4 py-10 text-center text-slate-500">Không có dữ liệu phù hợp.</td></tr>@endforelse
</tbody></table></div>
<div class="mt-5">{{ $entities->links() }}</div>
@endsection
