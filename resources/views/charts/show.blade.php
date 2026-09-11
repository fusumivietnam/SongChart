@extends('layouts.frontend')
@php($activeNav = 'discover')
@php
    $providerLabel = match ($chart['provider'] ?? null) {
        'youtube' => 'YouTube',
        null => null,
        default => ucfirst((string) $chart['provider']),
    };
    $metricLabel = match ($chart['metric']) {
        'youtube_video_view_count' => 'Lượt xem video',
        default => str($chart['metric'])->replace('_', ' ')->headline()->toString(),
    };
    $unitLabel = match ($chart['metric_unit']) {
        'views' => 'lượt xem',
        'streams' => 'lượt nghe',
        'count' => 'lượt',
        default => (string) $chart['metric_unit'],
    };
@endphp

@section('content')
<section class="sc-home-section">
    <div class="sc-container">
        <div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_20rem] lg:items-start">
            <div>
                <p class="sc-caption">BẢNG XẾP HẠNG SONGCHART</p>
                <h1 class="sc-section-title mt-2">{{ $metricLabel }}{{ $providerLabel ? ' · '.$providerLabel : '' }}</h1>
                <p class="mt-3 max-w-3xl text-sm leading-6 text-[var(--sc-text-secondary)]">
                    Xếp hạng từ các quan sát đã được lưu cùng nguồn gốc và thời điểm đo. SongChart không tự tạo số liệu phổ biến, lượt nghe hay thứ hạng khi chưa có bằng chứng.
                </p>
            </div>

            <aside class="rounded-[var(--sc-radius-panel)] border border-[var(--sc-border)] bg-white p-5" aria-label="Trạng thái dữ liệu bảng xếp hạng">
                @if($chart['state'] === 'unavailable')
                    <p class="font-bold">Chưa có đủ bằng chứng</p>
                    <p class="mt-2 text-sm leading-6 text-[var(--sc-text-secondary)]">Chart này đã được định nghĩa, nhưng chưa có snapshot đã lưu để công bố.</p>
                @else
                    <div class="flex items-center justify-between gap-3">
                        <p class="font-bold">Độ mới dữ liệu</p>
                        <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $chart['freshness'] === 'fresh' ? 'bg-emerald-50 text-emerald-700' : ($chart['freshness'] === 'stale' ? 'bg-amber-50 text-amber-700' : 'bg-slate-100 text-slate-600') }}">
                            {{ $chart['freshness'] === 'fresh' ? 'Mới' : ($chart['freshness'] === 'stale' ? 'Đã cũ' : 'Chưa xác định') }}
                        </span>
                    </div>
                    <p class="mt-2 text-sm leading-6 text-[var(--sc-text-secondary)]">Snapshot: <time datetime="{{ $chart['snapshot_at'] }}">{{ $chart['snapshot_at'] }}</time></p>
                @endif

                <dl class="mt-4 grid gap-3 border-t border-[var(--sc-border)] pt-4 text-sm">
                    <div>
                        <dt class="text-[var(--sc-text-muted)]">Metric</dt>
                        <dd class="mt-1 font-medium">{{ $metricLabel }} · {{ $unitLabel }}</dd>
                    </div>
                    <div>
                        <dt class="text-[var(--sc-text-muted)]">Ngữ nghĩa dữ liệu</dt>
                        <dd class="mt-1 break-words font-medium">{{ $chart['metric_semantics_version'] }}</dd>
                    </div>
                </dl>
            </aside>
        </div>

        @if($chart['state'] === 'unavailable')
            <div class="mt-8 rounded-[var(--sc-radius-panel)] border border-dashed border-[var(--sc-border)] bg-white p-8 text-center">
                <h2 class="text-lg font-bold">Chưa thể công bố thứ hạng</h2>
                <p class="mx-auto mt-2 max-w-2xl text-sm leading-6 text-[var(--sc-text-secondary)]">Không có snapshot đã persisted. Trạng thái này khác với một snapshot hợp lệ có giá trị bằng 0 hoặc không có hàng xếp hạng.</p>
            </div>
        @elseif(count($chart['rows']) === 0)
            <div class="mt-8 rounded-[var(--sc-radius-panel)] border border-[var(--sc-border)] bg-white p-8 text-center">
                <h2 class="text-lg font-bold">Snapshot hợp lệ, chưa có mục xếp hạng</h2>
                <p class="mx-auto mt-2 max-w-2xl text-sm leading-6 text-[var(--sc-text-secondary)]">Snapshot này đã được lưu và có provenance hợp lệ, nhưng không chứa recording nào để xếp hạng.</p>
            </div>
        @else
            <ol class="mt-8 grid gap-4" aria-label="Các vị trí trong bảng xếp hạng">
                @foreach($chart['rows'] as $row)
                    <li class="rounded-[var(--sc-radius-panel)] border border-[var(--sc-border)] bg-white p-5 md:p-6">
                        <div class="grid gap-4 md:grid-cols-[4rem_minmax(0,1fr)_auto] md:items-start">
                            <div class="text-2xl font-bold">#{{ $row['rank'] }}</div>
                            <div class="min-w-0">
                                <a class="text-lg font-bold hover:text-[var(--sc-primary)]" href="{{ $row['url'] }}">{{ $row['title'] }}</a>
                                <p class="mt-1 text-sm text-[var(--sc-text-secondary)]">
                                    {{ $row['evidence_count'] }} quan sát đã lưu
                                    @if($row['latest_observed_at'])
                                        · gần nhất <time datetime="{{ $row['latest_observed_at'] }}">{{ $row['latest_observed_at'] }}</time>
                                    @endif
                                </p>
                            </div>
                            <div class="md:text-right">
                                <p class="text-xl font-bold">{{ number_format($row['score'], 0, ',', '.') }}</p>
                                <p class="text-sm text-[var(--sc-text-secondary)]">{{ $unitLabel }}</p>
                                @if($row['value_state'] === 'observed_zero')
                                    <p class="mt-1 text-xs font-semibold text-[var(--sc-text-muted)]">Giá trị 0 đã được quan sát</p>
                                @endif
                            </div>
                        </div>

                        <details class="mt-4 border-t border-[var(--sc-border)] pt-4">
                            <summary class="cursor-pointer text-sm font-semibold">Nguồn và provenance</summary>
                            <div class="mt-3 grid gap-3">
                                @forelse($row['observations'] as $observation)
                                    <div class="rounded-xl bg-[var(--sc-surface-subtle)] p-4 text-sm">
                                        <div class="flex flex-wrap items-center justify-between gap-2">
                                            <p class="font-semibold">{{ ucfirst($observation['provider']) ?: 'Nguồn chưa xác định' }}</p>
                                            @if($observation['observed_at'])
                                                <time class="text-[var(--sc-text-muted)]" datetime="{{ $observation['observed_at'] }}">{{ $observation['observed_at'] }}</time>
                                            @endif
                                        </div>
                                        <p class="mt-2 text-[var(--sc-text-secondary)]">{{ number_format($observation['value'], 0, ',', '.') }} {{ $unitLabel }} · {{ $observation['metric_semantics_version'] }}</p>
                                        @if($observation['source_reference'])
                                            <p class="mt-1 break-all text-xs text-[var(--sc-text-muted)]">Nguồn tham chiếu: {{ $observation['source_reference'] }}</p>
                                        @endif
                                    </div>
                                @empty
                                    <p class="text-sm text-[var(--sc-text-secondary)]">Snapshot không cung cấp observation provenance chi tiết cho hàng này.</p>
                                @endforelse
                            </div>
                        </details>
                    </li>
                @endforeach
            </ol>
        @endif

        <p class="mt-6 text-sm leading-6 text-[var(--sc-text-muted)]">Mỗi vị trí chỉ được công bố từ snapshot persisted, calculation-versioned và retained observation provenance. Provider là nguồn bằng chứng, không phải canonical identity của recording.</p>
    </div>
</section>
@endsection
