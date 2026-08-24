@extends('layouts.frontend')
@php($activeNav = 'account')
@section('content')
<div class="sc-container py-12 md:py-16"><div class="mx-auto max-w-md"><h1 class="sc-page-title">Xác nhận mật khẩu</h1><p class="mt-2 text-[var(--sc-text-secondary)]">Thao tác bảo mật này yêu cầu xác nhận lại mật khẩu.</p><form method="POST" action="{{ route('password.confirm') }}" class="mt-6 space-y-4 rounded-[var(--sc-radius-card)] border bg-white p-6">@csrf<x-ui.input id="confirm-password" name="password" type="password" label="Mật khẩu" required autocomplete="current-password" /><x-ui.button type="submit" class="w-full">Xác nhận</x-ui.button></form></div></div>
@endsection
