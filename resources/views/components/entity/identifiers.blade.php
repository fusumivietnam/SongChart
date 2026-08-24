@props(['identifiers' => []])
<section aria-labelledby="entity-identifiers-title">
<h2 id="entity-identifiers-title" class="sc-section-title">Định danh</h2>
@if(count($identifiers))
<dl class="mt-4 space-y-3">@foreach($identifiers as $identifier)<div><dt class="text-xs font-semibold text-[var(--sc-text-muted)]">{{ $identifier['scheme'] }}</dt><dd class="mt-1 break-all font-mono text-sm">{{ $identifier['value'] }}</dd></div>@endforeach</dl>
@else
<p class="mt-3 text-sm text-[var(--sc-text-muted)]">Chưa có định danh bên ngoài đã được xác minh.</p>
@endif
</section>
