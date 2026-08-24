@props(['shape' => 'line'])
@php $shapes = ['line' => 'h-4 w-full rounded', 'title' => 'h-7 w-2/3 rounded', 'avatar' => 'h-12 w-12 rounded-full', 'card' => 'h-36 w-full rounded-[var(--sc-radius-card)]']; @endphp
<div aria-hidden="true" {{ $attributes->class('animate-pulse bg-slate-200 '.($shapes[$shape] ?? $shapes['line'])) }}></div>
