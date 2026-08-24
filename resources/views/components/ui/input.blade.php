@props([
    'label',
    'name',
    'type' => 'text',
    'value' => null,
    'hint' => null,
    'error' => null,
    'required' => false,
    'disabled' => false,
])
@php $id = $attributes->get('id', $name); @endphp
<div class="space-y-1.5">
    <label for="{{ $id }}" class="block text-sm font-semibold text-[var(--sc-text-primary)]">
        {{ $label }} @if($required)<span aria-hidden="true" class="text-[var(--sc-danger)]">*</span>@endif
    </label>
    <input id="{{ $id }}" name="{{ $name }}" type="{{ $type }}" value="{{ old($name, $value) }}"
        @required($required) @disabled($disabled) aria-invalid="{{ $error ? 'true' : 'false' }}"
        @if($hint || $error) aria-describedby="{{ $id }}-description" @endif
        {{ $attributes->except(['id'])->class([
            'min-h-11 w-full rounded-[var(--sc-radius-control)] border bg-white px-3.5 py-2.5 text-sm text-[var(--sc-text-primary)] shadow-sm transition placeholder:text-[var(--sc-text-muted)] focus:border-[var(--sc-primary)] focus:outline-none focus:ring-4 focus:ring-[color-mix(in_srgb,var(--sc-focus)_18%,transparent)] disabled:bg-[var(--sc-bg-subtle)] disabled:text-[var(--sc-text-muted)]',
            'border-[var(--sc-danger)]' => $error,
            'border-[var(--sc-border-strong)]' => !$error,
        ]) }}>
    @if($error || $hint)
        <p id="{{ $id }}-description" class="text-sm {{ $error ? 'text-[var(--sc-danger)]' : 'text-[var(--sc-text-secondary)]' }}">{{ $error ?: $hint }}</p>
    @endif
</div>
