@props(['label', 'variant' => 'secondary', 'size' => 'md', 'type' => 'button', 'disabled' => false])
@php
    $sizes = ['sm' => 'h-9 w-9', 'md' => 'h-11 w-11', 'lg' => 'h-12 w-12'];
    $variants = [
        'secondary' => 'border-[var(--sc-border-strong)] bg-white text-[var(--sc-text-primary)] hover:bg-[var(--sc-bg-subtle)]',
        'ghost' => 'border-transparent bg-transparent text-[var(--sc-text-secondary)] hover:bg-[var(--sc-bg-subtle)] hover:text-[var(--sc-text-primary)]',
        'danger' => 'border-transparent bg-[var(--sc-danger-soft)] text-[var(--sc-danger)] hover:brightness-95',
    ];
@endphp
<button type="{{ $type }}" aria-label="{{ $label }}" @disabled($disabled)
    {{ $attributes->class('inline-grid place-items-center rounded-[var(--sc-radius-control)] border transition disabled:pointer-events-none disabled:opacity-50 '.($sizes[$size] ?? $sizes['md']).' '.($variants[$variant] ?? $variants['secondary'])) }}>
    {{ $slot }}
</button>
