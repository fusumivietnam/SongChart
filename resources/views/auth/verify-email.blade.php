@extends('layouts.frontend')
@php($activeNav = 'account')
@section('content')
<div class="sc-container py-12 md:py-16"><div class="mx-auto max-w-lg rounded-[var(--sc-radius-card)] border bg-white p-6 shadow-[var(--sc-shadow-card)]">
    <h1 class="sc-page-title">Xác minh email</h1><p class="mt-3 text-[var(--sc-text-secondary)]">Hãy mở liên kết SongChart gửi tới <strong>{{ auth()->user()->email }}</strong> trước khi truy cập khu vực tài khoản.</p>
    @if(session('status') === 'verification-link-sent')<x-ui.alert class="mt-5" variant="success">Đã gửi lại liên kết xác minh.</x-ui.alert>@endif
    <div class="mt-6 flex flex-wrap gap-3"><form method="POST" action="{{ route('verification.send') }}">@csrf<x-ui.button type="submit">Gửi lại email</x-ui.button></form><form method="POST" action="{{ route('logout') }}">@csrf<x-ui.button type="submit" variant="secondary">Đăng xuất</x-ui.button></form></div>
</div></div>
@endsection
