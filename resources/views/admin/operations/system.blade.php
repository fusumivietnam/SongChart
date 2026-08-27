@extends('layouts.admin')
@section('content')
<x-admin.page-header :title="$title" :description="$description" />
@if(session('status'))<div class="mt-4 rounded-xl border border-emerald-200 bg-emerald-50 p-3 text-sm text-emerald-900">{{ session('status') }}</div>@endif

<section id="api-integrations" class="mt-6" aria-labelledby="api-integrations-heading">
    <div class="mb-4 flex flex-wrap items-end justify-between gap-3">
        <div>
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Thiết lập hệ thống</p>
            <h2 id="api-integrations-heading" class="mt-1 text-xl font-bold">API & tích hợp</h2>
            <p class="mt-1 max-w-3xl text-sm leading-6 text-slate-500">Quản lý thông tin kết nối dùng bởi các nguồn dữ liệu. Giá trị lưu trong Admin DB ưu tiên hơn <code>.env</code>; <code>.env</code> chỉ là fallback/bootstrap. Secret được mã hóa và không hiển thị lại.</p>
        </div>
        <a href="{{ route('admin.providers.index') }}" class="rounded-xl border border-slate-300 px-4 py-2.5 text-sm font-semibold">Mở trạng thái nguồn dữ liệu</a>
    </div>

    @can('manage-providers')
        <div class="grid gap-4 xl:grid-cols-2">
            @foreach($providers as $provider)
                @if(in_array($provider->slug, ['musicbrainz', 'youtube'], true))
                    @php($configuration = is_array($provider->configuration) ? $provider->configuration : [])
                    @php($secretKeys = is_array($configuration['_secrets'] ?? null) ? array_keys($configuration['_secrets']) : [])
                    @php($hasAdminSecret = $provider->slug === 'youtube' && in_array('api_key', $secretKeys, true))
                    @php($hasEnvYoutubeKey = $provider->slug === 'youtube' && (string) config('songchart.providers.youtube.api_key', '') !== '')
                    @php($musicBrainzFallback = (string) config('songchart.providers.musicbrainz.user_agent', 'SongChartWeb/1.0 (contact@example.com)'))
                    <x-ui.card>
                        <div class="p-5">
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <h3 class="font-bold">{{ $provider->name }}</h3>
                                    <p class="mt-1 text-sm text-slate-500">{{ $provider->slug === 'musicbrainz' ? 'Nhận diện ứng dụng khi gọi MusicBrainz API.' : 'Credential cho YouTube Data API.' }}</p>
                                </div>
                                <x-ui.badge :variant="$provider->is_enabled ? 'success' : 'warning'">{{ $provider->is_enabled ? 'Nguồn đang bật' : 'Nguồn đang tắt' }}</x-ui.badge>
                            </div>

                            @if($provider->slug === 'youtube')
                                <div class="mt-4 rounded-xl border p-3 text-sm {{ $hasAdminSecret ? 'border-emerald-200 bg-emerald-50 text-emerald-900' : ($hasEnvYoutubeKey ? 'border-blue-200 bg-blue-50 text-blue-900' : 'border-amber-200 bg-amber-50 text-amber-900') }}">
                                    @if($hasAdminSecret)
                                        Credential hiện tại: <strong>Admin DB (mã hóa)</strong>. Đây là giá trị hiệu lực ưu tiên.
                                    @elseif($hasEnvYoutubeKey)
                                        Credential hiện tại: <strong>.env fallback</strong>. Nhập key bên dưới để chuyển quyền quản lý sang Admin.
                                    @else
                                        Credential hiện tại: <strong>chưa cấu hình</strong>.
                                    @endif
                                </div>
                            @endif

                            <form method="POST" action="{{ route('admin.providers.configuration.update', $provider) }}" class="mt-5 space-y-4">
                                @csrf
                                @if($provider->slug === 'musicbrainz')
                                    <label class="block text-sm font-semibold">MusicBrainz User-Agent
                                        <input name="musicbrainz_user_agent" value="{{ old('musicbrainz_user_agent', (string) ($configuration['user_agent'] ?? $musicBrainzFallback)) }}" class="mt-1.5 w-full rounded-xl border border-slate-300 px-3 py-2.5" placeholder="SongChartWeb/1.0 (contact@your-domain.com)">
                                    </label>
                                    <p class="text-xs leading-5 text-slate-500">Khuyến nghị: <code>SongChartWeb/1.0 (contact@your-domain.com)</code>. Chỉ cần thay email liên hệ thật; rate limit và timeout dùng policy hệ thống.</p>
                                @else
                                    <label class="block text-sm font-semibold">YouTube Data API key
                                        <input type="password" name="youtube_api_key" autocomplete="new-password" class="mt-1.5 w-full rounded-xl border border-slate-300 px-3 py-2.5" placeholder="{{ $hasAdminSecret ? 'Đã cấu hình — để trống nếu không đổi' : 'Dán API key tại đây' }}">
                                    </label>
                                    <p class="text-xs leading-5 text-slate-500">Secret chỉ được ghi mới khi có giá trị; form không đọc ngược credential đã lưu.</p>
                                @endif

                                <label class="block text-sm font-semibold">Lý do thay đổi
                                    <input name="rationale" required minlength="10" maxlength="2000" class="mt-1.5 w-full rounded-xl border border-slate-300 px-3 py-2.5" placeholder="Ví dụ: Cấu hình tích hợp cho môi trường demo vận hành">
                                </label>
                                <input type="hidden" name="idempotency_key" value="{{ (string) str()->uuid() }}">
                                <div class="flex flex-wrap items-center gap-3">
                                    <button class="rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white">Lưu thiết lập</button>
                                    <a href="{{ route('admin.providers.show', $provider) }}" class="text-sm font-semibold underline">Kiểm tra provider</a>
                                </div>
                            </form>
                        </div>
                    </x-ui.card>
                @endif
            @endforeach
        </div>
        <div class="mt-4 rounded-xl border border-slate-200 bg-slate-50 p-4 text-sm leading-6 text-slate-600">Các tích hợp chưa có adapter vận hành hoàn chỉnh sẽ chưa mở form credential. Khi adapter được triển khai, credential phải đi vào khu Thiết lập hệ thống này thay vì thêm biến cấu hình rải rác trong màn nguồn dữ liệu.</div>
    @else
        <x-ui.card><div class="p-5"><h3 class="font-bold">API & tích hợp</h3><p class="mt-2 text-sm leading-6 text-slate-600">Tài khoản hiện tại không có quyền <code>manage-providers</code>. Super Admin hoặc Provider Manager mới có thể thay đổi credential và cấu hình tích hợp.</p></div></x-ui.card>
    @endcan
</section>

<section class="mt-8" aria-labelledby="system-health-heading">
    <div class="mb-4"><h2 id="system-health-heading" class="text-xl font-bold">Sức khỏe hệ thống</h2><p class="mt-1 text-sm text-slate-500">Thông tin runtime read-only phục vụ chẩn đoán vận hành.</p></div>
    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3" data-admin-section="system-health">@foreach($checks as $check)<x-ui.card><p class="text-sm text-slate-500">{{ $check['label'] }}</p><p class="mt-2 text-xl font-bold">{{ $check['value'] }}</p><p class="mt-2 text-xs uppercase tracking-wide text-slate-400">{{ $check['state'] }}</p></x-ui.card>@endforeach</div>
</section>
@endsection
