@props(['item'])
<article class="sc-card flex h-full flex-col p-5">
    <div class="flex flex-wrap items-center gap-2">
        <x-ui.badge :variant="$item['type']">{{ $item['label'] }}</x-ui.badge>
        <x-ui.badge variant="{{ $item['verified'] ? 'success' : 'warning' }}">{{ $item['verified'] ? 'Đã xác minh' : 'Chưa xác minh đầy đủ' }}</x-ui.badge>
    </div>
    <h3 class="mt-4 text-xl font-bold">{{ $item['title'] }}</h3>
    <p class="mt-2 text-sm font-medium text-[var(--sc-text-secondary)]">{{ $item['context'] }}</p>
    <p class="mt-2 text-sm leading-6 text-[var(--sc-text-muted)]">{{ $item['description'] }}</p>
    <a class="mt-auto pt-5 font-semibold text-[var(--sc-primary)]" href="{{ $item['url'] }}">Xem hồ sơ <span aria-hidden="true">→</span></a>
</article>
