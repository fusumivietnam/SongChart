@props(['variant' => 'neutral'])
@php
    $variants = [
        'neutral' => 'bg-[var(--sc-bg-subtle)] text-[var(--sc-text-secondary)]',
        'primary' => 'bg-[var(--sc-primary-soft)] text-[var(--sc-primary)]',
        'success' => 'bg-[var(--sc-success-soft)] text-[var(--sc-success)]',
        'warning' => 'bg-[var(--sc-warning-soft)] text-[var(--sc-warning)]',
        'danger' => 'bg-[var(--sc-danger-soft)] text-[var(--sc-danger)]',
        'info' => 'bg-[var(--sc-info-soft)] text-[var(--sc-info)]',
        'artist' => 'bg-violet-100 text-violet-700',
        'recording' => 'bg-blue-100 text-blue-700',
        'release' => 'bg-emerald-100 text-emerald-700',
        'version' => 'bg-purple-100 text-purple-700',
        'work' => 'bg-amber-100 text-amber-700',
        'collection' => 'bg-indigo-100 text-indigo-700',
    ];
@endphp
<span {{ $attributes->class('inline-flex min-h-6 items-center rounded-full px-2.5 py-1 text-xs font-semibold '.($variants[$variant] ?? $variants['neutral'])) }}>{{ $slot }}</span>
