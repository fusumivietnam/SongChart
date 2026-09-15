@extends('layouts.admin')
@php($activeAdminNav = 'dashboard')
@section('content')
<div data-admin-dashboard="attention-first">
    <x-admin.page-header title="Tổng quan" description="Những việc cần chú ý trước, sau đó mới đến số liệu và chi tiết kỹ thuật.">
        <x-slot:actions>
            <div class="flex flex-wrap gap-2">
                @can('manage-providers')
                    <a href="{{ route('admin.system.index') }}#api-integrations"><x-ui.button size="sm">Thiết lập API & tích hợp</x-ui.button></a>
                    <a href="{{ route('admin.imports.preview') }}"><x-ui.button size="sm" variant="secondary">Nhập dữ liệu</x-ui.button></a>
                @endcan
                <a href="{{ route('home') }}"><x-ui.button size="sm" variant="secondary">Mở trang công khai</x-ui.button></a>
            </div>
        </x-slot:actions>
    </x-admin.page-header>

    @cannot('manage-providers')
        <div class="mt-4 rounded-xl border border-[var(--admin-warning)] bg-[var(--admin-warning-soft)] p-4 text-sm text-[var(--admin-text-primary)]">
            Tài khoản hiện tại có quyền vào Admin nhưng chưa có quyền <strong>manage-providers</strong>, nên thiết lập API/tích hợp và thao tác nguồn dữ liệu đang ở chế độ chỉ đọc.
        </div>
    @endcannot

    <section class="mt-2" aria-labelledby="attention-heading" data-dashboard-section="attention-center">
        <x-ui.card><div class="p-5"><div class="flex items-start justify-between gap-4"><div><h2 id="attention-heading" class="text-xl font-bold">Cần xử lý</h2><p class="mt-1 text-sm text-[var(--admin-text-secondary)]">Ưu tiên các mục có hành động rõ ràng.</p></div></div><div class="mt-5 divide-y divide-[var(--admin-border)]">@foreach($workItems as $item)<div class="flex flex-wrap items-center gap-4 py-4 first:pt-0 last:pb-0" data-work-item="{{ $item['key'] }}" data-severity="{{ $item['severity'] }}"><span class="grid h-10 w-10 flex-none place-items-center rounded-xl bg-[var(--admin-bg-subtle)] text-[var(--admin-text-secondary)]"><x-icons.icon name="shield" /></span><span class="min-w-0 flex-1"><strong class="block text-sm">{{ $item['label'] }}</strong><small class="mt-1 block text-[var(--admin-text-secondary)]">{{ $item['description'] }}</small></span><span class="text-xl font-extrabold">{{ number_format($item['count']) }}</span><a class="rounded border border-[var(--admin-border)] px-3 py-2 text-sm font-semibold" href="{{ $item['href'] }}">{{ $item['action'] }}</a></div>@endforeach</div></div></x-ui.card>
    </section>

    <section class="mt-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4" aria-label="Tình trạng dữ liệu" data-dashboard-section="metrics">@foreach($metrics as $metric)<x-ui.card><div data-dashboard-metric="{{ $metric['key'] }}" class="flex h-full flex-col"><p class="text-sm font-semibold text-[var(--admin-text-secondary)]">{{ $metric['label'] }}</p><p class="mt-3 text-3xl font-extrabold tracking-tight">{{ number_format($metric['value']) }}</p><p class="mt-auto pt-3 text-xs text-[var(--admin-text-secondary)]">{{ $metric['note'] }}</p></div></x-ui.card>@endforeach</section>

    <div class="mt-6 grid gap-6 xl:grid-cols-2">
        <section aria-labelledby="provider-heading"><x-ui.card><div class="p-5"><div class="flex items-start justify-between gap-4"><div><h2 id="provider-heading" class="text-xl font-bold">Nguồn dữ liệu</h2><p class="mt-1 text-sm text-[var(--admin-text-secondary)]">Theo dõi trạng thái provider; credential và thông tin kết nối được quản lý riêng trong Thiết lập hệ thống.</p></div>@can('manage-providers')<a class="text-sm font-semibold underline" href="{{ route('admin.providers.index') }}">Xem nguồn</a>@endcan</div><div class="mt-5 space-y-3">@forelse($providers->take(6) as $provider)<div class="flex items-center gap-3 rounded-xl bg-[var(--admin-bg-subtle)] p-3"><span class="h-2.5 w-2.5 rounded-full {{ $provider->is_enabled ? 'bg-[var(--admin-success)]' : 'bg-[var(--admin-warning)]' }}"></span><div class="min-w-0 flex-1"><p class="truncate text-sm font-semibold">{{ $provider->name }}</p><p class="text-xs text-[var(--admin-text-secondary)]">{{ $provider->is_enabled ? 'Đang sử dụng' : 'Đang tạm ngừng' }}</p></div></div>@empty<x-ui.empty-state title="Chưa có nguồn dữ liệu" description="Nguồn dữ liệu sẽ xuất hiện ở đây sau khi được đăng ký." />@endforelse</div></div></x-ui.card></section>
        <section aria-labelledby="notice-heading"><x-ui.card><div class="p-5"><h2 id="notice-heading" class="text-xl font-bold">Tình trạng chung</h2><div class="mt-4 space-y-3">@foreach($systemNotices as $notice)<div class="rounded-xl border border-[var(--admin-border)] p-4" data-severity="{{ $notice['severity'] }}"><p class="text-sm font-bold">{{ $notice['title'] }}</p><p class="mt-1 text-sm leading-6 text-[var(--admin-text-secondary)]">{{ $notice['description'] }}</p></div>@endforeach</div></div></x-ui.card></section>
    </div>

    <details class="mt-6 rounded-xl border border-[var(--admin-border)] bg-[var(--admin-bg-surface)] p-5" data-admin-technical-details>
        <summary class="cursor-pointer font-semibold">Chi tiết kỹ thuật</summary>
        <p class="mt-2 text-sm text-[var(--admin-text-secondary)]">Dành cho chẩn đoán vận hành. Người quản trị thông thường không cần dùng phần này.</p>

        <section class="mt-5 rounded-xl border border-[var(--admin-border)] bg-[var(--admin-bg-subtle)] p-4" data-development-intelligence="{{ $developmentIntelligence['status'] }}">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div>
                    <h3 class="font-semibold">Development Intelligence</h3>
                    <p class="mt-1 text-xs text-[var(--admin-text-secondary)]">Snapshot source/architecture chỉ được build nền hoặc khi SA/Dev/Tech yêu cầu; trang này không scan source.</p>
                </div>
                <span class="rounded-full border border-[var(--admin-border)] bg-[var(--admin-bg-surface)] px-2.5 py-1 text-xs font-semibold">{{ strtoupper($developmentIntelligence['status']) }}</span>
            </div>
            @if($developmentIntelligence['available'])
                <div class="mt-4 grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
                    <div><p class="text-xs text-[var(--admin-text-secondary)]">Source</p><p class="mt-1 font-mono text-sm">{{ $developmentIntelligence['short_sha'] ?? 'unknown' }}</p></div>
                    <div><p class="text-xs text-[var(--admin-text-secondary)]">Classes</p><p class="mt-1 text-lg font-bold">{{ number_format($developmentIntelligence['metrics']['class_nodes'] ?? 0) }}</p></div>
                    <div><p class="text-xs text-[var(--admin-text-secondary)]">Routes</p><p class="mt-1 text-lg font-bold">{{ number_format($developmentIntelligence['metrics']['route_nodes'] ?? 0) }}</p></div>
                    <div><p class="text-xs text-[var(--admin-text-secondary)]">Graph edges</p><p class="mt-1 text-lg font-bold">{{ number_format($developmentIntelligence['metrics']['edges'] ?? 0) }}</p></div>
                </div>
                <p class="mt-3 text-xs text-[var(--admin-text-secondary)]">Snapshot {{ $developmentIntelligence['generated_at'] }} · {{ $developmentIntelligence['branch'] ?? 'detached' }}</p>
            @else
                <p class="mt-3 text-sm text-[var(--admin-text-secondary)]">{{ $developmentIntelligence['message'] }}</p>
            @endif
        </section>

        <div class="mt-5 grid gap-6 xl:grid-cols-2"><div><h3 class="font-semibold">Đồng bộ gần đây</h3><div class="mt-3 space-y-2">@forelse($recentSyncs->take(6) as $sync)<div class="rounded border border-[var(--admin-border)] p-3 text-sm"><strong>{{ $sync->provider?->name ?? 'Nguồn không xác định' }}</strong> · {{ $sync->operation }}<div class="text-xs text-[var(--admin-text-secondary)]">{{ $sync->status }} · {{ $sync->processed_count }} xử lý · {{ $sync->failed_count }} lỗi</div></div>@empty<p class="text-sm text-[var(--admin-text-secondary)]">Chưa có lịch sử đồng bộ.</p>@endforelse</div></div><div><h3 class="font-semibold">Hoạt động tiện ích gần đây</h3><div class="mt-3 space-y-2">@forelse($recentOperations as $operation)<div class="rounded border border-[var(--admin-border)] p-3 text-sm"><strong>{{ $operation->extension?->name ?? 'System operation' }}</strong> · {{ $operation->type }}<div class="text-xs text-[var(--admin-text-secondary)]">{{ $operation->status }}</div></div>@empty<p class="text-sm text-[var(--admin-text-secondary)]">Chưa có hoạt động.</p>@endforelse</div></div></div>
    </details>
</div>
@endsection
