@props(['media'])

@if($media)
<section aria-labelledby="recording-media-title" data-recording-media data-media-state="{{ $media['state'] ?? 'unknown' }}">
    <x-ui.card>
        <div class="p-5">
            @if(($media['state'] ?? null) === 'no_selection')
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <p class="sc-caption">MEDIA</p>
                        <h2 id="recording-media-title" class="mt-1 text-xl font-bold">Media đã xác minh</h2>
                    </div>
                    <x-ui.badge variant="warning">{{ $media['availability_label'] }}</x-ui.badge>
                </div>

                <p class="mt-3 text-sm leading-6 text-[var(--sc-text-secondary)]">{{ $media['availability_reason'] }}</p>
                <p class="mt-2 text-xs leading-5 text-[var(--sc-text-muted)]">{{ $media['selection_reason'] }}</p>
            @else
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <p class="sc-caption">MEDIA</p>
                        <h2 id="recording-media-title" class="mt-1 text-xl font-bold">Xem video đã xác minh</h2>
                    </div>
                    <x-ui.badge :variant="$media['can_embed'] ? 'success' : ($media['fresh'] ? 'neutral' : 'warning')">{{ $media['availability_label'] }}</x-ui.badge>
                </div>

                <p class="mt-3 text-sm leading-6 text-[var(--sc-text-secondary)]">{{ $media['availability_reason'] }}</p>
                <p class="mt-2 text-xs leading-5 text-[var(--sc-text-muted)]">{{ $media['selection_reason'] }}</p>

                @if($media['can_embed'] && $media['embed_url'])
                    <div class="mt-5 overflow-hidden rounded-[var(--sc-radius-card)] border border-[var(--sc-border)] bg-black">
                        <div class="aspect-video">
                            <iframe
                                class="h-full w-full"
                                src="{{ $media['embed_url'] }}"
                                title="{{ $media['title'] }}"
                                loading="lazy"
                                referrerpolicy="strict-origin-when-cross-origin"
                                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                                allowfullscreen></iframe>
                        </div>
                    </div>
                @endif

                <div class="mt-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div class="text-sm text-[var(--sc-text-secondary)]">
                        <p class="font-semibold text-[var(--sc-text)]">{{ $media['title'] }}</p>
                        @if($media['channel_title'])<p class="mt-1">Kênh: {{ $media['channel_title'] }}</p>@endif
                        <p class="mt-1 text-xs text-[var(--sc-text-muted)]">Kiểm tra gần nhất: {{ $media['checked_at'] ?? 'Chưa ghi nhận' }}</p>
                    </div>
                    @if($media['url'])
                        <x-ui.button variant="provider" :href="$media['url']" target="_blank" rel="noopener noreferrer external">Mở trên {{ $media['provider'] }} <span aria-hidden="true">↗</span></x-ui.button>
                    @endif
                </div>

                <p class="mt-4 border-t border-[var(--sc-border)] pt-4 text-xs leading-5 text-[var(--sc-text-muted)]">Nội dung được phát trực tiếp từ {{ $media['provider'] }}. SongChart không lưu trữ hoặc re-host video và chỉ nhúng destination đã được duyệt, còn fresh và cho phép embed.</p>
            @endif
        </div>
    </x-ui.card>
</section>
@endif
