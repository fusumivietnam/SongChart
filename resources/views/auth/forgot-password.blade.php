@extends('layouts.frontend')
@php($activeNav = 'account')
@section('content')
<div class="sc-container py-12 md:py-16"><div class="mx-auto max-w-md">
    <h1 class="sc-page-title">Khôi phục mật khẩu</h1><p class="mt-2 text-[var(--sc-text-secondary)]">Nhập email. Phản hồi luôn trung tính để hạn chế dò tìm tài khoản.</p>
    @if(session('status'))<x-ui.alert class="mt-5" variant="success">{{ session('status') }}</x-ui.alert>@endif
    <form method="POST" action="{{ route('password.email') }}" class="mt-6 space-y-4 rounded-[var(--sc-radius-card)] border bg-white p-6 shadow-[var(--sc-shadow-card)]">@csrf
        <x-ui.input id="forgot-email" name="email" type="email" label="Email" :value="old('email')" required autocomplete="email" />
        <x-ui.button type="submit" class="w-full">Gửi liên kết khôi phục</x-ui.button>
    </form>
</div></div>
@endsection
