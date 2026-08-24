@props([
    'variant' => 'primary',
    'size' => 'md',
    'type' => 'button',
    'href' => null,
    'disabled' => false,
])

@php
    $variants = [
        'primary' => 'border-transparent bg-[var(--sc-primary)] text-white hover:bg-[var(--sc-primary-hover)]',
        'secondary' => 'border-[var(--sc-border-strong)] bg-white text-[var(--sc-text-primary)] hover:bg-[var(--sc-bg-subtle)]',
        'ghost' => 'border-transparent bg-transparent text-[var(--sc-text-primary)] hover:bg-[var(--sc-bg-subtle)]',
        'danger' => 'border-transparent bg-[var(--sc-danger)] text-white hover:brightness-95',
        'provider' => 'border-[var(--sc-border-strong)] bg-white text-[var(--sc-text-primary)] hover:border-[var(--sc-primary-border)] hover:bg-[var(--sc-primary-soft)]',
    ];
    $sizes = [
        'sm' => 'min-h-9 px-3 py-1.5 text-sm',
        'md' => 'min-h-11 px-4 py-2.5 text-sm',
        'lg' => 'min-h-12 px-5 py-3 text-base',
    ];
    $classes = 'inline-flex items-center justify-center gap-2 rounded-[var(--sc-radius-control)] border font-semibold transition focus-visible:outline-none disabled:pointer-events-none disabled:opacity-50 aria-disabled:pointer-events-none aria-disabled:opacity-50 '.($variants[$variant] ?? $variants['primary']).' '.($sizes[$size] ?? $sizes['md']);
@endphp

@if($href)
    <a href="{{ $disabled ? null : $href }}" aria-disabled="{{ $disabled ? 'true' : 'false' }}" {{ $attributes->class($classes) }}>{{ $slot }}</a>
@else
    <button type="{{ $type }}" @disabled($disabled) {{ $attributes->class($classes) }}>{{ $slot }}</button>
@endif
