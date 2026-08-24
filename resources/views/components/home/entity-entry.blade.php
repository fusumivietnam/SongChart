@props(['entry'])
<a href="{{ route('search', ['q' => $entry['example'], 'type' => $entry['type']]) }}"
   class="group rounded-[var(--sc-radius-card)] border border-[var(--sc-border)] bg-white p-5 shadow-[var(--sc-shadow-card)] transition hover:-translate-y-0.5 hover:border-[var(--sc-primary-border)] hover:shadow-[var(--sc-shadow-float)] focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-[color-mix(in_srgb,var(--sc-focus)_18%,transparent)]">
    <div class="flex items-start justify-between gap-4">
        <x-ui.badge :variant="$entry['type']">{{ $entry['label'] }}</x-ui.badge>
        <span class="text-lg text-[var(--sc-primary)] transition group-hover:translate-x-0.5" aria-hidden="true">→</span>
    </div>
    <h3 class="mt-4 text-lg font-bold">{{ $entry['label'] }}</h3>
    <p class="mt-2 text-sm leading-6 text-[var(--sc-text-secondary)]">{{ $entry['description'] }}</p>
    <p class="mt-4 text-xs font-semibold text-[var(--sc-text-muted)]">Ví dụ: {{ $entry['example'] }}</p>
</a>
