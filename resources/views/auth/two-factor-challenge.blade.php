@extends('layouts.frontend')
@php($activeNav = 'account')
@section('content')
<div class="sc-container py-12 md:py-16"><div class="mx-auto max-w-md"><h1 class="sc-page-title">Xác minh hai bước</h1><p class="mt-2 text-[var(--sc-text-secondary)]">Nhập mã TOTP hoặc một recovery code. Không nhập đồng thời cả hai.</p><form method="POST" action="{{ route('two-factor.login') }}" class="mt-6 space-y-4 rounded-[var(--sc-radius-card)] border bg-white p-6">@csrf<x-ui.input id="two-factor-code" name="code" inputmode="numeric" autocomplete="one-time-code" label="Mã xác thực" /><x-ui.input id="recovery-code" name="recovery_code" autocomplete="one-time-code" label="Recovery code" /><x-ui.button type="submit" class="w-full">Xác minh</x-ui.button></form></div></div>
@endsection
