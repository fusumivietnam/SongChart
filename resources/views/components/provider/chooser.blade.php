<section aria-labelledby="provider-chooser-title" data-provider-chooser>
<x-ui.card>
    <div class="flex flex-wrap items-start justify-between gap-3">
        <div>
            <p class="sc-caption">PROVIDER DESTINATIONS</p>
            <h2 id="provider-chooser-title" class="mt-1 text-xl font-bold">Chọn nơi nghe</h2>
        </div>
        <x-ui.badge variant="neutral">Không phát tại SongChart</x-ui.badge>
    </div>
    <p class="mt-3 text-sm leading-6 text-[var(--sc-text-secondary)]">SongChart chỉ điều hướng tới điểm đến bên ngoài. Khả dụng phụ thuộc thị trường, thời điểm và chính sách của từng provider.</p>

    <div class="mt-5 space-y-3">
        @foreach($providers as $provider)
            @php($canOpen = $provider['can_open'])
            <article class="rounded-[var(--sc-radius-control)] border border-[var(--sc-border)] p-4"
                data-provider-key="{{ $provider['key'] }}"
                data-provider-status="{{ $provider['status'] }}"
                data-compliance-state="{{ $provider['compliance_state'] }}">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                    <div class="min-w-0">
                        <div class="flex flex-wrap items-center gap-2">
                            <h3 class="font-semibold">{{ $provider['name'] }}</h3>
                            <x-ui.badge :variant="match($provider['status']) {'available' => 'success', 'stale' => 'warning', default => 'neutral'}">{{ $provider['status_label'] }}</x-ui.badge>
                        </div>
                        <p class="mt-2 text-sm text-[var(--sc-text-secondary)]">{{ $provider['availability_reason'] }}</p>
                        <dl class="mt-3 grid gap-x-4 gap-y-2 text-xs text-[var(--sc-text-muted)] sm:grid-cols-2">
                            <div><dt class="font-semibold text-[var(--sc-text-secondary)]">Thị trường</dt><dd>{{ $provider['market'] }}</dd></div>
                            <div><dt class="font-semibold text-[var(--sc-text-secondary)]">Kiểm tra</dt><dd>{{ $provider['checked_at'] ?? 'Chưa kiểm tra' }}</dd></div>
                            <div><dt class="font-semibold text-[var(--sc-text-secondary)]">Khả năng</dt><dd>{{ $provider['capability_label'] }}</dd></div>
                            <div><dt class="font-semibold text-[var(--sc-text-secondary)]">Attribution</dt><dd>{{ $provider['attribution'] }}</dd></div>
                        </dl>
                    </div>
                    <div class="shrink-0">
                        @if($canOpen)
                            <x-ui.button variant="provider" :href="$provider['url']" target="_blank" rel="noopener noreferrer external" aria-label="Mở {{ $provider['name'] }} trong tab mới">{{ $provider['action_label'] }} <span aria-hidden="true">↗</span></x-ui.button>
                        @else
                            <x-ui.button variant="provider" disabled aria-label="{{ $provider['name'] }} hiện không khả dụng">Không khả dụng</x-ui.button>
                        @endif
                    </div>
                </div>
                @if(! $canOpen)
                    <p class="mt-3 border-t border-[var(--sc-border)] pt-3 text-xs text-[var(--sc-text-muted)]">Không mở liên kết vì destination chưa được xác minh đầy đủ, đã cũ hoặc chưa vượt qua compliance gate.</p>
                @endif
            </article>
        @endforeach
    </div>

    <div class="mt-5 rounded-[var(--sc-radius-control)] bg-[var(--sc-bg-subtle)] p-4 text-xs leading-5 text-[var(--sc-text-secondary)]">
        <strong>Trước khi rời SongChart:</strong> provider có thể áp dụng đăng nhập, vùng lãnh thổ, quảng cáo hoặc điều khoản riêng. SongChart không bảo đảm nội dung tiếp tục khả dụng sau thời điểm kiểm tra.
    </div>
</x-ui.card>
</section>
