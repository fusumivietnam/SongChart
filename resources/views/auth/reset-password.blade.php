@extends('layouts.frontend')
@php($activeNav = 'account')
@section('content')
<div class="sc-container py-12 md:py-16"><div class="mx-auto max-w-md">
    <h1 class="sc-page-title">Đặt mật khẩu mới</h1>
    <form method="POST" action="{{ route('password.update') }}" class="mt-6 space-y-4 rounded-[var(--sc-radius-card)] border bg-white p-6 shadow-[var(--sc-shadow-card)]">@csrf
        <input type="hidden" name="token" value="{{ $request->route('token') }}">
        <x-ui.input id="reset-email" name="email" type="email" label="Email" :value="old('email', $request->email)" required autocomplete="username" />
        <x-ui.input id="reset-password" name="password" type="password" label="Mật khẩu mới" required autocomplete="new-password" />
        <x-ui.input id="reset-password-confirmation" name="password_confirmation" type="password" label="Nhập lại mật khẩu mới" required autocomplete="new-password" />
        <x-ui.button type="submit" class="w-full">Cập nhật mật khẩu</x-ui.button>
    </form>
</div></div>
@endsection
