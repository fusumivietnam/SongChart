@extends('layouts.frontend')
@section('title', $entity['title'])
@section('content')
<div class="sc-container py-8 md:py-12" data-entity-type="{{ $entity['type'] }}" data-entity-slug="{{ $entity['slug'] }}">
<nav aria-label="Breadcrumb" class="text-sm text-[var(--sc-text-secondary)]"><a href="{{ route('home') }}">Trang chủ</a> <span aria-hidden="true">/</span> <a href="{{ route('search',['type'=>$entity['type'],'q'=>$entity['title']]) }}">{{ $entity['label'] }}</a> <span aria-hidden="true">/</span> <span aria-current="page">{{ $entity['title'] }}</span></nav>
<div class="mt-8 grid gap-8 lg:grid-cols-[minmax(0,1fr)_22rem]">
<main class="space-y-10">
<header class="flex flex-col gap-6 sm:flex-row">
<div class="grid h-32 w-32 shrink-0 place-items-center rounded-[var(--sc-radius-card)] bg-[var(--sc-primary-soft)] text-4xl font-bold text-[var(--sc-primary)]" aria-hidden="true">{{ mb_substr($entity['title'],0,1) }}</div>
<div><p class="text-sm font-semibold text-[var(--sc-primary)]">{{ $entity['eyebrow'] }}</p><div class="mt-2 flex flex-wrap gap-2"><x-ui.badge :variant="$entity['type']">{{ $entity['label'] }}</x-ui.badge>@if($entity['verified'])<x-ui.badge variant="success">Canonical đã xác minh</x-ui.badge>@else<x-ui.badge variant="warning">Một số thông tin chưa được xác minh</x-ui.badge>@endif</div><h1 class="mt-3 text-4xl font-bold tracking-tight">{{ $entity['title'] }}</h1><p class="mt-2 text-lg text-[var(--sc-text-secondary)]">{{ $entity['context'] }}</p><p class="mt-3 text-sm text-[var(--sc-text-muted)]">{{ $entity['meta'] }}</p></div>
</header>
<section aria-labelledby="entity-overview-title"><h2 id="entity-overview-title" class="sc-section-title">Tổng quan</h2><p class="mt-3 max-w-3xl leading-7 text-[var(--sc-text-secondary)]">{{ $entity['description'] }}</p><div class="mt-5"><x-entity.facts :facts="$entity['facts']" /></div></section>
<section aria-labelledby="entity-passport-title" data-entity-passport>
<h2 id="entity-passport-title" class="sc-section-title">Data passport</h2>
<div class="mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
@foreach([
    ['Coverage', $entity['passport']['coverage'].'%'],
    ['Confidence', $entity['passport']['confidence'].'%'],
    ['Evidence', (string) $entity['passport']['assertion_count']],
    ['Open conflicts', (string) $entity['passport']['open_conflict_count']],
] as [$label,$value])
<x-ui.card><div class="p-4"><p class="text-xs font-semibold uppercase tracking-wide text-[var(--sc-text-secondary)]">{{ $label }}</p><p class="mt-2 text-2xl font-bold">{{ $value }}</p></div></x-ui.card>
@endforeach
</div>
<p class="mt-3 text-sm text-[var(--sc-text-secondary)]">{{ $entity['passport']['identifier_count'] }} external identifiers · {{ $entity['passport']['approved_destination_count'] }} approved destinations. Confidence is evidence-weighted and does not replace editorial review.</p>
</section>
<x-entity.relationships :title="$entity['relationship_title']" :items="$entity['relationships']" />
<section aria-labelledby="entity-provenance-title"><h2 id="entity-provenance-title" class="sc-section-title">Nguồn và provenance</h2><div class="mt-4 overflow-hidden rounded-[var(--sc-radius-card)] border border-[var(--sc-border)] bg-white"><table class="w-full text-left text-sm"><thead class="bg-[var(--sc-bg-subtle)]"><tr><th class="px-4 py-3">Nguồn</th><th class="px-4 py-3">Trạng thái</th><th class="px-4 py-3">Kiểm tra</th></tr></thead><tbody>@foreach($entity['sources'] as $source)<tr class="border-t border-[var(--sc-border)]"><td class="px-4 py-3 font-semibold">{{ $source['name'] }}</td><td class="px-4 py-3">{{ $source['status'] }}</td><td class="px-4 py-3">{{ $source['checked_at'] }}</td></tr>@endforeach</tbody></table></div></section>
</main>
<aside class="space-y-5"><x-provider.chooser :entity="$entity" /><x-ui.card><x-entity.identifiers :identifiers="$entity['identifiers']" /></x-ui.card></aside>
</div>
</div>
@endsection
