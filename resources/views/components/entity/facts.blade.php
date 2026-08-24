@props(['facts' => []])
@if(count($facts))
<dl class="grid gap-3 sm:grid-cols-2 xl:grid-cols-3">
@foreach($facts as $fact)
<div class="rounded-[var(--sc-radius-control)] border border-[var(--sc-border)] bg-[var(--sc-bg-subtle)] p-4">
<dt class="text-xs font-semibold uppercase tracking-wide text-[var(--sc-text-muted)]">{{ $fact['label'] }}</dt>
<dd class="mt-1 font-semibold text-[var(--sc-text-primary)]">{{ $fact['value'] }}</dd>
</div>
@endforeach
</dl>
@endif
