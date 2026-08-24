@props(['name', 'title', 'description' => null, 'open' => false])
<div x-data="{ open: @js($open) }" x-on:open-modal.window="if ($event.detail === @js($name)) open = true" x-on:keydown.escape.window="open = false" x-show="open" x-cloak class="fixed inset-0 z-50 grid place-items-center p-4">
    <div class="absolute inset-0 bg-slate-950/50" x-on:click="open = false" aria-hidden="true"></div>
    <section role="dialog" aria-modal="true" aria-labelledby="{{ $name }}-title" class="relative z-10 w-full max-w-lg rounded-[var(--sc-radius-panel)] border bg-white p-6 shadow-[var(--sc-shadow-float)]">
        <div class="flex items-start justify-between gap-4">
            <div><h2 id="{{ $name }}-title" class="text-xl font-bold">{{ $title }}</h2>@if($description)<p class="mt-1 text-sm text-[var(--sc-text-secondary)]">{{ $description }}</p>@endif</div>
            <x-ui.icon-button label="Đóng hộp thoại" variant="ghost" x-on:click="open = false">×</x-ui.icon-button>
        </div>
        <div class="mt-6">{{ $slot }}</div>
        @isset($footer)<div class="mt-6 flex justify-end gap-3 border-t pt-4">{{ $footer }}</div>@endisset
    </section>
</div>
