@extends('layouts.frontend')
@php($activeNav = 'account')
@section('content')
<x-account.shell active="overview" title="Tổng quan tài khoản" description="Trạng thái danh tính, quyền truy cập và bảo mật hiện tại.">
    <div class="grid gap-4 sm:grid-cols-2">
        <x-ui.card><p class="text-sm text-[var(--sc-text-secondary)]">Email</p><p class="mt-1 font-bold">{{ $user->email }}</p><div class="mt-3"><x-ui.badge :variant="$user->hasVerifiedEmail() ? 'success' : 'warning'">{{ $user->hasVerifiedEmail() ? 'Đã xác minh' : 'Chưa xác minh' }}</x-ui.badge></div></x-ui.card>
        <x-ui.card><p class="text-sm text-[var(--sc-text-secondary)]">Xác minh hai bước</p><p class="mt-1 font-bold">{{ $user->hasConfirmedTwoFactorAuthentication() ? 'Đang bật' : 'Chưa bật' }}</p><div class="mt-3"><x-ui.button href="{{ route('account.security') }}" variant="secondary">Quản lý bảo mật</x-ui.button></div></x-ui.card>
        <x-ui.card><p class="text-sm text-[var(--sc-text-secondary)]">Vai trò</p><p class="mt-1 font-bold">{{ $user->role->label() }}</p><p class="mt-2 text-sm text-[var(--sc-text-muted)]">Quyền được cấp phía server; giao diện không tự nâng quyền.</p></x-ui.card>
        <x-ui.card><p class="text-sm text-[var(--sc-text-secondary)]">Trạng thái</p><p class="mt-1 font-bold">{{ $user->is_active ? 'Đang hoạt động' : 'Đã vô hiệu hóa' }}</p><p class="mt-2 text-sm text-[var(--sc-text-muted)]">Tài khoản vô hiệu hóa không thể đăng nhập.</p></x-ui.card>
    </div>
</x-account.shell>
@endsection
