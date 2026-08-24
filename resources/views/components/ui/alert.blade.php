@props(['variant' => 'info', 'title' => null])
@php
    $variants = [
        'info' => ['bg-[var(--sc-info-soft)] text-[var(--sc-info)] border-blue-200', 'Thông tin'],
        'success' => ['bg-[var(--sc-success-soft)] text-[var(--sc-success)] border-green-200', 'Thành công'],
        'warning' => ['bg-[var(--sc-warning-soft)] text-[var(--sc-warning)] border-amber-200', 'Cảnh báo'],
        'danger' => ['bg-[var(--sc-danger-soft)] text-[var(--sc-danger)] border-red-200', 'Lỗi'],
    ];
    [$classes, $fallbackTitle] = $variants[$variant] ?? $variants['info'];
@endphp
<div role="{{ $variant === 'danger' ? 'alert' : 'status' }}" {{ $attributes->class('rounded-[var(--sc-radius-control)] border p-4 '.$classes) }}>
    <p class="font-semibold">{{ $title ?: $fallbackTitle }}</p>
    <div class="mt-1 text-sm leading-6 text-[var(--sc-text-secondary)]">{{ $slot }}</div>
</div>
