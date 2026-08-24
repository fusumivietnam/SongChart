@extends('layouts.frontend')
@php($activeNav = 'account')
@section('content')
<x-account.shell active="profile" title="Hồ sơ" description="Cập nhật tên hiển thị và địa chỉ email đăng nhập.">
    @if(session('status') === 'profile-information-updated')<x-ui.alert variant="success" class="mb-5">Đã cập nhật hồ sơ.</x-ui.alert>@endif
    <form method="POST" action="{{ route('user-profile-information.update') }}" class="space-y-4 rounded-[var(--sc-radius-card)] border bg-white p-6 shadow-[var(--sc-shadow-card)]">@csrf @method('PUT')
        <x-ui.input id="profile-name" name="name" label="Tên hiển thị" :value="$user->name" :error="$errors->updateProfileInformation->first('name')" required autocomplete="name" />
        <x-ui.input id="profile-email" name="email" type="email" label="Email" :value="$user->email" :error="$errors->updateProfileInformation->first('email')" required autocomplete="email" />
        <p class="text-sm text-[var(--sc-text-muted)]">Đổi email sẽ yêu cầu xác minh lại trước khi tiếp tục sử dụng khu vực tài khoản.</p>
        <x-ui.button type="submit">Lưu hồ sơ</x-ui.button>
    </form>
</x-account.shell>
@endsection
