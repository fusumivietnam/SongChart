@extends('layouts.frontend')
@php($activeNav = 'account')
@section('content')
<x-account.shell active="security" title="Bảo mật" description="Quản lý mật khẩu, xác minh hai bước và phiên đăng nhập.">
    <div class="space-y-6">
        <section class="rounded-[var(--sc-radius-card)] border bg-white p-6 shadow-[var(--sc-shadow-card)]" data-security-section="password">
            <h2 class="sc-section-title">Đổi mật khẩu</h2>
            @if(session('status') === 'password-updated')<x-ui.alert variant="success" class="mt-4">Đã cập nhật mật khẩu.</x-ui.alert>@endif
            <form method="POST" action="{{ route('user-password.update') }}" class="mt-5 space-y-4">@csrf @method('PUT')
                <x-ui.input id="current-password" name="current_password" type="password" label="Mật khẩu hiện tại" :error="$errors->updatePassword->first('current_password')" required autocomplete="current-password" />
                <x-ui.input id="new-password" name="password" type="password" label="Mật khẩu mới" :error="$errors->updatePassword->first('password')" required autocomplete="new-password" />
                <x-ui.input id="new-password-confirmation" name="password_confirmation" type="password" label="Nhập lại mật khẩu mới" required autocomplete="new-password" />
                <x-ui.button type="submit">Cập nhật mật khẩu</x-ui.button>
            </form>
        </section>

        <section class="rounded-[var(--sc-radius-card)] border bg-white p-6 shadow-[var(--sc-shadow-card)]" data-security-section="two-factor" data-two-factor-confirmed="{{ $user->hasConfirmedTwoFactorAuthentication() ? 'true' : 'false' }}">
            <div class="flex flex-wrap items-start justify-between gap-4"><div><h2 class="sc-section-title">Xác minh hai bước</h2><p class="mt-2 text-sm text-[var(--sc-text-secondary)]">Dùng ứng dụng TOTP. Admin và operator phải bật 2FA trước thao tác nhạy cảm.</p></div><x-ui.badge :variant="$user->hasConfirmedTwoFactorAuthentication() ? 'success' : 'warning'">{{ $user->hasConfirmedTwoFactorAuthentication() ? 'Đang bật' : 'Chưa bật' }}</x-ui.badge></div>

            @if($user->hasPendingTwoFactorAuthentication())
                <div class="mt-5 rounded-[var(--sc-radius-control)] bg-[var(--sc-bg-subtle)] p-4">
                    <p class="font-semibold">Quét QR rồi xác nhận mã</p>
                    <div class="mt-4 max-w-48">{!! $user->twoFactorQrCodeSvg() !!}</div>
                    <form method="POST" action="{{ route('two-factor.confirm') }}" class="mt-4 flex flex-col gap-3 sm:flex-row">@csrf
                        <x-ui.input id="two-factor-confirm-code" name="code" label="Mã 6 chữ số" inputmode="numeric" autocomplete="one-time-code" required />
                        <x-ui.button type="submit" class="self-end">Xác nhận</x-ui.button>
                    </form>
                </div>
            @endif

            <div class="mt-5 flex flex-wrap gap-3">
                @if(! $user->hasTwoFactorAuthenticationConfigured())
                    <form method="POST" action="{{ route('two-factor.enable') }}">@csrf<x-ui.button type="submit">Bật 2FA</x-ui.button></form>
                @else
                    <form method="POST" action="{{ route('two-factor.disable') }}">@csrf @method('DELETE')<x-ui.button type="submit" variant="danger">Tắt 2FA</x-ui.button></form>
                @endif
            </div>

            @if($user->hasConfirmedTwoFactorAuthentication())
                <details class="mt-5"><summary class="cursor-pointer font-semibold">Recovery codes</summary><p class="mt-2 text-sm text-[var(--sc-text-secondary)]">Lưu ngoại tuyến. Mỗi mã chỉ dùng một lần.</p><ul class="mt-3 grid gap-2 font-mono text-sm sm:grid-cols-2">@foreach($user->recoveryCodes() as $code)<li class="rounded bg-[var(--sc-bg-subtle)] px-3 py-2">{{ $code }}</li>@endforeach</ul><form method="POST" action="{{ route('two-factor.recovery-codes') }}" class="mt-4">@csrf<x-ui.button type="submit" variant="secondary">Tạo lại recovery codes</x-ui.button></form></details>
            @endif
        </section>

        <section class="rounded-[var(--sc-radius-card)] border bg-white p-6 shadow-[var(--sc-shadow-card)]"><h2 class="sc-section-title">Phiên hiện tại</h2><p class="mt-2 text-sm text-[var(--sc-text-secondary)]">Đăng xuất sẽ vô hiệu hóa phiên trình duyệt hiện tại.</p><form method="POST" action="{{ route('logout') }}" class="mt-4">@csrf<x-ui.button type="submit" variant="secondary">Đăng xuất</x-ui.button></form></section>
    </div>
</x-account.shell>
@endsection
