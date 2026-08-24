@props(['title', 'description' => null])
<div {{ $attributes->class('rounded-[var(--sc-radius-card)] border border-dashed border-[var(--sc-border-strong)] bg-[var(--sc-bg-surface)] px-6 py-10 text-center') }}>
    @isset($icon)<div class="mx-auto mb-4 grid h-12 w-12 place-items-center rounded-full bg-[var(--sc-primary-soft)] text-[var(--sc-primary)]">{{ $icon }}</div>@endisset
    <h3 class="text-lg font-bold">{{ $title }}</h3>
    @if($description)<p class="mx-auto mt-2 max-w-lg text-sm leading-6 text-[var(--sc-text-secondary)]">{{ $description }}</p>@endif
    @isset($actions)<div class="mt-5 flex flex-wrap justify-center gap-3">{{ $actions }}</div>@endisset
</div>
