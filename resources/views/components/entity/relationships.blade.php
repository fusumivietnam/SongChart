@props(['title', 'items' => []])
<section aria-labelledby="entity-relationships-title">
<h2 id="entity-relationships-title" class="sc-section-title">{{ $title }}</h2>
@if(count($items))
<div class="mt-4 divide-y divide-[var(--sc-border)] rounded-[var(--sc-radius-card)] border border-[var(--sc-border)] bg-white px-5">
@foreach($items as $item)
<x-entity.result-row :item="$item" />
@endforeach
</div>
@else
<x-ui.empty-state title="Chưa có quan hệ đã xác minh" description="Quan hệ canonical sẽ xuất hiện tại đây khi được kiểm chứng." />
@endif
</section>
