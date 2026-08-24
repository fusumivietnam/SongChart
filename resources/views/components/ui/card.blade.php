@props(['padding' => 'md', 'elevated' => false])
@php $paddings = ['none' => '', 'sm' => 'p-4', 'md' => 'p-5 md:p-6', 'lg' => 'p-6 md:p-8']; @endphp
<section {{ $attributes->class('rounded-[var(--sc-radius-card)] border border-[var(--sc-border)] bg-[var(--sc-bg-surface)] '.($elevated ? 'shadow-[var(--sc-shadow-float)]' : 'shadow-[var(--sc-shadow-card)]').' '.($paddings[$padding] ?? $paddings['md'])) }}>{{ $slot }}</section>
