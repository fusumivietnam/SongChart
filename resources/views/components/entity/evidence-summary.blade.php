@props(['entity'])
@php
    $passport = $entity['passport'];
    $bridge = $passport['identity_bridge'];
    $plan = $passport['enrichment_plan'];
@endphp
<section aria-labelledby="entity-evidence-title" data-entity-passport>
    <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="sc-caption">CANONICAL EVIDENCE</p>
            <h2 id="entity-evidence-title" class="sc-section-title mt-1">Độ tin cậy dữ liệu</h2>
        </div>
        @if($passport['open_conflict_count'] > 0)
            <x-ui.badge variant="warning">{{ $passport['open_conflict_count'] }} xung đột cần xem xét</x-ui.badge>
        @else
            <x-ui.badge variant="success">Không có xung đột mở</x-ui.badge>
        @endif
    </div>

    <p class="mt-3 max-w-3xl text-sm leading-6 text-[var(--sc-text-secondary)]">
        SongChart giữ thực thể canonical tách biệt với định danh và điểm đến của provider. Các chỉ số dưới đây mô tả mức độ bằng chứng hiện có, không phải độ nổi tiếng hay xếp hạng.
    </p>

    <dl class="mt-5 grid gap-3 sm:grid-cols-3">
        @foreach([
            ['Mức độ bao phủ', $passport['coverage'].'%', 'Phần dữ liệu canonical hiện có bằng chứng hỗ trợ.'],
            ['Độ tin cậy', $passport['confidence'].'%', 'Ước lượng dựa trên bằng chứng; không thay thế kiểm duyệt.'],
            ['Bằng chứng', (string) $passport['assertion_count'], 'Số assertion đang hỗ trợ thực thể này.'],
        ] as [$label, $value, $description])
            <div class="rounded-[var(--sc-radius-control)] border border-[var(--sc-border)] bg-[var(--sc-bg-subtle)] p-4">
                <dt class="text-xs font-semibold uppercase tracking-wide text-[var(--sc-text-muted)]">{{ $label }}</dt>
                <dd>
                    <span class="mt-1 block text-2xl font-bold text-[var(--sc-text-primary)]">{{ $value }}</span>
                    <span class="mt-2 block text-xs leading-5 text-[var(--sc-text-secondary)]">{{ $description }}</span>
                </dd>
            </div>
        @endforeach
    </dl>

    <div class="mt-6 grid gap-4 md:grid-cols-2" data-identity-bridge>
        <x-ui.card>
            <p class="sc-caption">EXTERNAL IDENTIFIERS</p>
            <h3 class="mt-1 font-semibold">Định danh đối chiếu</h3>
            <p class="mt-2 text-sm leading-6 text-[var(--sc-text-secondary)]">{{ $bridge['connected_source_count'] }} nguồn identity đang kết nối với cùng một thực thể SongChart.</p>
            <ul class="mt-4 space-y-2 break-words text-sm text-[var(--sc-text-secondary)]">
                @forelse($bridge['identifiers'] as $identity)
                    <li><strong class="text-[var(--sc-text-primary)]">{{ $identity['namespace'] }}</strong>: {{ $identity['value'] }}</li>
                @empty
                    <li>Chưa có định danh ngoài nào được xác minh.</li>
                @endforelse
            </ul>
        </x-ui.card>

        <x-ui.card>
            <p class="sc-caption">PROVIDER CONTEXT</p>
            <h3 class="mt-1 font-semibold">Trạng thái điểm đến</h3>
            <p class="mt-2 text-sm leading-6 text-[var(--sc-text-secondary)]">{{ $passport['approved_destination_count'] }} điểm đến đã được phê duyệt. Provider chỉ là ngữ cảnh và hành động ra ngoài, không phải canonical identity.</p>
            <ul class="mt-4 space-y-2 text-sm text-[var(--sc-text-secondary)]">
                @forelse($bridge['destinations'] as $destination)
                    <li class="flex items-center justify-between gap-3"><span class="font-medium text-[var(--sc-text-primary)]">{{ $destination['provider'] }}</span><span>{{ $destination['review_state'] }}</span></li>
                @empty
                    <li>Chưa có điểm đến provider được liên kết.</li>
                @endforelse
            </ul>
        </x-ui.card>
    </div>

    <details class="mt-5 rounded-[var(--sc-radius-control)] border border-[var(--sc-border)] bg-white" data-enrichment-plan>
        <summary class="cursor-pointer px-4 py-3 font-semibold">Tình trạng hoàn thiện dữ liệu</summary>
        <div class="border-t border-[var(--sc-border)] px-4 py-4 text-sm text-[var(--sc-text-secondary)]">
            <p>Recipe completeness: {{ $plan['completeness'] }}%. Đây là trạng thái read-only; trang public không gọi provider và không tự ghi đè canonical fields.</p>
            @if(count($plan['needs']))
                <ul class="mt-3 space-y-2">
                    @foreach($plan['needs'] as $need)
                        <li><strong class="text-[var(--sc-text-primary)]">{{ $need['kind'] }} · {{ $need['key'] }}</strong> — {{ $need['reason'] }}</li>
                    @endforeach
                </ul>
            @else
                <p class="mt-3">Không có khoảng trống enrichment bắt buộc trong recipe hiện tại.</p>
            @endif
        </div>
    </details>
</section>
