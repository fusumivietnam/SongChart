@php($summary = $destinationAttention['summary'] ?? ['total' => 0, 'attention' => 0, 'unknown' => 0, 'stale' => 0, 'unavailable' => 0])
@if(($summary['total'] ?? 0) > 0)
<section class="mt-6" aria-labelledby="provider-destination-attention" data-admin-provider-destination-attention>
    <x-ui.card>
        <div class="p-5">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Destination quality</p>
                    <h2 id="provider-destination-attention" class="mt-1 text-lg font-semibold">Destination cần chú ý</h2>
                    <p class="mt-1 max-w-3xl text-sm text-slate-500">Destination chưa được kiểm tra, đã quá hạn hoặc không còn đủ evidence công khai sẽ không được public selection ưu tiên. Có thể kiểm tra lại từng destination qua provider mà không thay đổi canonical identity hoặc review approval.</p>
                </div>
                <div class="grid grid-cols-4 gap-2 text-center text-xs">
                    <div class="rounded border p-2"><strong class="block text-base">{{ $summary['attention'] }}</strong>Cần xử lý</div>
                    <div class="rounded border p-2"><strong class="block text-base">{{ $summary['unknown'] }}</strong>Chưa kiểm tra</div>
                    <div class="rounded border p-2"><strong class="block text-base">{{ $summary['stale'] }}</strong>Quá hạn</div>
                    <div class="rounded border p-2"><strong class="block text-base">{{ $summary['unavailable'] }}</strong>Không khả dụng</div>
                </div>
            </div>

            <div class="mt-5 overflow-x-auto">
                <table class="min-w-[1100px] w-full text-left text-sm">
                    <thead class="text-xs uppercase tracking-wide text-slate-500">
                        <tr><th class="py-2 pr-3">Destination</th><th class="py-2 pr-3">Trạng thái</th><th class="py-2 pr-3">Lần kiểm tra</th><th class="py-2 pr-3">Evidence cần rà soát</th><th class="py-2">Bước tiếp theo</th></tr>
                    </thead>
                    <tbody class="divide-y">
                    @foreach(($destinationAttention['items'] ?? []) as $item)
                        <tr>
                            <td class="py-3 pr-3"><strong>{{ $item['title'] ?: $item['resource_id'] }}</strong><div class="font-mono text-xs text-slate-500">{{ $item['entity_type'] }} · {{ $item['resource_id'] }}</div></td>
                            <td class="py-3 pr-3"><x-ui.badge :variant="$item['tone']">{{ $item['status_label'] }}</x-ui.badge><div class="mt-1 text-xs text-slate-500">review: {{ $item['review_state'] }} · privacy: {{ $item['privacy_status'] ?? 'unknown' }}</div></td>
                            <td class="py-3 pr-3">{{ $item['last_checked_at']?->format('d/m/Y H:i') ?? 'Chưa ghi nhận' }}</td>
                            <td class="py-3 pr-3">@if($item['reasons'] !== [])<ul class="space-y-1 text-xs text-slate-600">@foreach($item['reasons'] as $reason)<li>• {{ $reason }}</li>@endforeach</ul>@else<span class="text-xs text-slate-500">Không có lỗi eligibility.</span>@endif</td>
                            <td class="py-3 text-sm">
                                <p>{{ $item['next_action'] }}</p>
                                @if($provider->slug === 'youtube' && $item['attention'])
                                    @can('manage-providers')
                                        <form class="mt-3 space-y-2" method="POST" action="{{ route('admin.providers.mutate', $provider) }}">
                                            @csrf
                                            <input type="hidden" name="action" value="destination_reverify">
                                            <input type="hidden" name="destination_id" value="{{ $item['id'] }}">
                                            <input type="hidden" name="idempotency_key" value="{{ (string) str()->uuid() }}">
                                            <label class="block text-xs font-medium">Lý do kiểm tra lại
                                                <input name="rationale" required minlength="10" maxlength="2000" class="mt-1 w-full rounded border p-2 text-sm" placeholder="Ví dụ: destination đã stale, cần refresh evidence">
                                            </label>
                                            <button class="rounded border px-3 py-2 text-xs font-semibold" type="submit">Kiểm tra lại</button>
                                        </form>
                                    @endcan
                                @endif
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </x-ui.card>
</section>
@endif
