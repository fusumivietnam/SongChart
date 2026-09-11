<section aria-labelledby="provider-chooser-title" data-provider-chooser>
<x-ui.card>
    <div class="flex flex-wrap items-start justify-between gap-3">
        <div>
            <p class="sc-caption">PROVIDER DESTINATIONS</p>
            <h2 id="provider-chooser-title" class="mt-1 text-xl font-bold">Nơi nghe / xem</h2>
        </div>
        <x-ui.badge variant="neutral">Mở bên ngoài SongChart</x-ui.badge>
    </div>
    <p class="mt-3 text-sm leading-6 text-[var(--sc-text-secondary)]">Các điểm đến dưới đây thuộc provider bên ngoài. Khả dụng không quyết định canonical identity và có thể thay đổi theo thị trường hoặc thời điểm.</p>

    <div class="mt-5 space-y-3">
        @foreach($providers as $provider)
            @php($canOpen = $provider['can_open'])
            <article class="rounded-[var(--sc-radius-control)] border border-[var(--sc-border)] p-4"
                data-provider-key="{{ $provider['key'] }}"
                data-provider-status="{{ $provider['status'] }}"
                data-compliance-state="{{ $provider['compliance_state'] }}">
                <div class="flex flex-col gap-4">
                    <div class="min-w-0">
                        <div class="flex flex-wrap items-center gap-2">
                            <h3 class="font-semibold">{{ $provider['name'] }}</h3>
                            <x-ui.badge :variant="match($provider['status']) {'available' => 'success', 'stale' => 'warning', default => 'neutral'}">{{ $provider['status_label'] }}</x-ui.badge>
                        </div>
                        <p class="mt-1 text-xs font-medium text-[var(--sc-text-muted)]">{{ $provider['capability_label'] }}</p>
                        <p class="mt-3 text-sm leading-6 text-[var(--sc-text-secondary)]">{{ $provider['availability_reason'] }}</p>
                        <dl class="mt-3 grid grid-cols-2 gap-3 text-xs text-[var(--sc-text-muted)]">
                            <div><dt class="font-semibold text-[var(--sc-text-secondary)]">Thị trường</dt><dd class="mt-1">{{ $provider['market'] }}</dd></div>
                            <div><dt class="font-semibold text-[var(--sc-text-secondary)]">Kiểm tra</dt><dd class="mt-1">{{ $provider['checked_at'] ?? 'Chưa kiểm tra' }}</dd></div>
                        </dl>
                    </div>
                    @if($canOpen)
                        <x-ui.button class="w-full justify-center" variant="provider" :href="$provider['url']" target="_blank" rel="noopener noreferrer external" aria-label="Mở {{ $provider['name'] }} trong tab mới">{{ $provider['action_label'] }} <span aria-hidden="true">↗</span></x-ui.button>
                    @else
                        <x-ui.button class="w-full justify-center" variant="provider" disabled aria-label="{{ $provider['name'] }} hiện không khả dụng">Không khả dụng</x-ui.button>
                        <p class="text-xs leading-5 text-[var(--sc-text-muted)]">Liên kết bị khóa vì destination chưa vượt qua đầy đủ trạng thái xác minh/compliance hiện tại.</p>
                    @endif
                </div>
                <p class="mt-3 border-t border-[var(--sc-border)] pt-3 text-xs text-[var(--sc-text-muted)]">Attribution: {{ $provider['attribution'] }}</p>
            </article>
        @endforeach
    </div>

    <div class="mt-5 rounded-[var(--sc-radius-control)] bg-[var(--sc-bg-subtle)] p-4 text-xs leading-5 text-[var(--sc-text-secondary)]">
        <strong>Trước khi rời SongChart:</strong> provider có thể áp dụng đăng nhập, vùng lãnh thổ, quảng cáo hoặc điều khoản riêng. SongChart không phát nội dung và không bảo đảm điểm đến tiếp tục khả dụng sau lần kiểm tra gần nhất.
    </div>
</x-ui.card>
</section>
