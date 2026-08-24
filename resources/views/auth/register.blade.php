@extends('layouts.frontend')
@php($activeNav = 'account')
@section('content')
<div class="sc-container py-12 md:py-16"><div class="mx-auto max-w-md">
    <p class="text-sm font-semibold text-[var(--sc-primary)]">Tài khoản SongChart</p><h1 class="sc-page-title mt-1">Tạo tài khoản</h1>
    <p class="mt-2 text-[var(--sc-text-secondary)]">Tài khoản công khai mặc định không có quyền quản trị.</p>
    @if($errors->any())<x-ui.alert class="mt-5" variant="danger">Vui lòng kiểm tra lại các trường bên dưới.</x-ui.alert>@endif
    <form method="POST" action="{{ route('register') }}" class="mt-6 space-y-4 rounded-[var(--sc-radius-card)] border border-[var(--sc-border)] bg-white p-6 shadow-[var(--sc-shadow-card)]">
        @csrf
        <x-ui.input id="register-name" name="name" label="Tên hiển thị" :value="old('name')" required autocomplete="name" />
        <x-ui.input id="register-email" name="email" type="email" label="Email" :value="old('email')" required autocomplete="username" />
        <x-ui.input id="register-password" name="password" type="password" label="Mật khẩu" required autocomplete="new-password" />
        <x-ui.input id="register-password-confirmation" name="password_confirmation" type="password" label="Nhập lại mật khẩu" required autocomplete="new-password" />
        <label class="flex items-start gap-2 text-sm leading-6"><input type="checkbox" name="terms" value="1" required class="mt-1 rounded border-[var(--sc-border-strong)]"><span>Tôi đồng ý với điều khoản sử dụng và chính sách quyền riêng tư hiện hành.</span></label>
        <x-ui.button type="submit" class="w-full">Tạo tài khoản</x-ui.button>
        <p class="text-center text-sm">Đã có tài khoản? <a class="font-semibold text-[var(--sc-primary)]" href="{{ route('login') }}">Đăng nhập</a></p>
    </form>
</div></div>
@endsection
