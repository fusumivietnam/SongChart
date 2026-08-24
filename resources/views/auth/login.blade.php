@extends('layouts.frontend')
@php($activeNav = 'account')
@section('content')
<div class="sc-container py-12 md:py-16">
    <div class="mx-auto max-w-md">
        <p class="text-sm font-semibold text-[var(--sc-primary)]">Tài khoản SongChart</p>
        <h1 class="sc-page-title mt-1">Đăng nhập</h1>
        <p class="mt-2 text-[var(--sc-text-secondary)]">Tiếp tục quản lý hồ sơ, bảo mật và tùy chọn tài khoản.</p>
        @if(session('status'))<x-ui.alert class="mt-5" variant="success">{{ session('status') }}</x-ui.alert>@endif
        @if($errors->any())<x-ui.alert class="mt-5" variant="danger">Thông tin đăng nhập không hợp lệ hoặc tài khoản không hoạt động.</x-ui.alert>@endif
        <form method="POST" action="{{ route('login') }}" class="mt-6 space-y-4 rounded-[var(--sc-radius-card)] border border-[var(--sc-border)] bg-white p-6 shadow-[var(--sc-shadow-card)]">
            @csrf
            <x-ui.input id="login-email" name="email" type="email" label="Email" :value="old('email')" required autofocus autocomplete="username" />
            <x-ui.input id="login-password" name="password" type="password" label="Mật khẩu" required autocomplete="current-password" />
            <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="remember" value="1" class="rounded border-[var(--sc-border-strong)]"> Ghi nhớ đăng nhập</label>
            <x-ui.button type="submit" class="w-full">Đăng nhập</x-ui.button>
            <div class="flex flex-wrap justify-between gap-3 text-sm">
                <a class="font-semibold text-[var(--sc-primary)]" href="{{ route('password.request') }}">Quên mật khẩu?</a>
                <a class="font-semibold text-[var(--sc-primary)]" href="{{ route('register') }}">Tạo tài khoản</a>
            </div>
        </form>
    </div>
</div>
@endsection
