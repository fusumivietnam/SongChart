@props(['active' => 'dashboard'])
@php
$user = auth()->user();
$canManageCatalog = $user?->can('manage-catalog') ?? false;
$canManageProviders = $user?->can('manage-providers') ?? false;
$canReviewIdentity = $user?->can('manage-identity-conflicts') ?? false;
$canManageSystem = $user?->can('manage-extensions') ?? false;
$canViewAudit = $user?->can('view-audit') ?? false;

$groups = [
    ['label' => null, 'items' => [
        ['key' => 'dashboard', 'label' => 'Tổng quan', 'icon' => 'dashboard', 'href' => route('admin.dashboard')],
        ['key' => 'catalog', 'label' => 'Nội dung', 'icon' => 'database', 'href' => route('admin.catalog.index')],
    ]],
    ['label' => 'Vận hành dữ liệu', 'items' => array_values(array_filter([
        $canManageCatalog ? ['key' => 'canonical-admissions', 'label' => 'Canonical admission', 'icon' => 'shield', 'href' => route('admin.canonical-admissions.index')] : null,
        $canManageProviders ? ['key' => 'providers', 'label' => 'Nguồn dữ liệu', 'icon' => 'external', 'href' => route('admin.providers.index')] : null,
        $canManageProviders ? ['key' => 'imports', 'label' => 'Tác vụ dữ liệu', 'icon' => 'collection', 'href' => route('admin.imports.index')] : null,
        $canManageProviders ? ['key' => 'quarantine', 'label' => 'Dữ liệu cần rà soát', 'icon' => 'shield', 'href' => route('admin.quarantine.index')] : null,
        $canReviewIdentity ? ['key' => 'identity-conflicts', 'label' => 'Xung đột định danh', 'icon' => 'shield', 'href' => route('admin.identity-conflicts.index')] : null,
    ]))],
    ['label' => 'Quản trị', 'items' => array_values(array_filter([
        ['key' => 'users', 'label' => 'Người dùng & quyền', 'icon' => 'user', 'href' => route('admin.users.index')],
        $canManageSystem ? ['key' => 'extensions', 'label' => 'Tiện ích hệ thống', 'icon' => 'plug', 'href' => route('admin.extensions.index')] : null,
        $canManageSystem ? ['key' => 'system', 'label' => 'Sức khỏe hệ thống', 'icon' => 'settings', 'href' => route('admin.system.index')] : null,
        $canViewAudit ? ['key' => 'audit', 'label' => 'Nhật ký đặc quyền', 'icon' => 'shield', 'href' => route('admin.audit.index')] : null,
    ]))],
];
@endphp
<aside class="admin-sidebar" :class="{'is-open': sidebarOpen}" aria-label="Điều hướng quản trị">
    <div class="admin-sidebar-brand">
        <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3">
            <span class="sc-brand-mark">SC</span>
            <span class="font-bold">SongChart Admin</span>
        </a>
        <button class="admin-sidebar-close lg:hidden" type="button" @click="sidebarOpen=false" aria-label="Đóng menu"><x-icons.icon name="close" /></button>
    </div>
    <nav class="admin-sidebar-nav">
        @foreach($groups as $group)
            @if($group['items'] !== [])
                <div class="admin-nav-group">
                    @if($group['label'])<p class="admin-nav-label">{{ $group['label'] }}</p>@endif
                    @foreach($group['items'] as $item)
                        <a href="{{ $item['href'] }}" data-admin-nav="{{ $item['key'] }}" @class(['admin-nav-item', 'is-active' => $active === $item['key']])>
                            <x-icons.icon :name="$item['icon']" class="h-5 w-5" />
                            <span>{{ $item['label'] }}</span>
                        </a>
                    @endforeach
                </div>
            @endif
        @endforeach
    </nav>
    <div class="admin-sidebar-footer">
        <p class="text-xs text-white/45">SongChartWeb 0.1.0-dev</p>
        <p class="mt-1 text-xs text-white/70">Vận hành theo công việc · Stage 16.5</p>
    </div>
</aside>
