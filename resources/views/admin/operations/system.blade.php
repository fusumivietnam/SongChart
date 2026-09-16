@extends('layouts.admin')
@section('content')
<x-admin.page-header :title="$title" :description="$description" />
@if(session('status'))<div class="mt-4 rounded-xl border border-emerald-200 bg-emerald-50 p-3 text-sm text-emerald-900">{{ session('status') }}</div>@endif

<section id="api-integrations" class="mt-6" aria-labelledby="api-integrations-heading">
    <div class="mb-4 flex flex-wrap items-end justify-between gap-3">
        <div>
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Thiết lập hệ thống</p>
            <h2 id="api-integrations-heading" class="mt-1 text-xl font-bold">API & tích hợp</h2>
            <p class="mt-1 max-w-3xl text-sm leading-6 text-slate-500">Cấu hình kết nối và trạng thái vận hành tại một nơi. Admin DB ưu tiên hơn <code>.env</code>; secret được mã hóa và không hiển thị lại.</p>
        </div>
        <a href="{{ route('admin.providers.index') }}" class="rounded-xl border border-slate-300 px-4 py-2.5 text-sm font-semibold">Mở nguồn dữ liệu</a>
    </div>

    @can('manage-providers')
        <div class="grid gap-4 xl:grid-cols-2">
            @forelse($providers->whereIn('slug', ['musicbrainz', 'youtube']) as $provider)
                @php($configuration = is_array($provider->configuration) ? $provider->configuration : [])
                @php($hasEnvYoutubeKey = $provider->slug === 'youtube' && (string) config('songchart.providers.youtube.api_key', '') !== '')
                @php($musicBrainzFallback = (string) config('songchart.providers.musicbrainz.user_agent', 'SongChartWeb/1.0 (contact@example.com)'))
                <x-ui.card>
                    <div class="p-5">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <h3 class="font-bold">{{ $provider->name }}</h3>
                                <p class="mt-1 text-sm text-slate-500">{{ $provider->slug === 'musicbrainz' ? 'Nhận diện ứng dụng khi gọi MusicBrainz API.' : 'Credential pool cho YouTube Data API.' }}</p>
                            </div>
                            <x-ui.badge :variant="$provider->is_enabled ? 'success' : 'warning'">{{ $provider->is_enabled ? 'Đang hoạt động' : 'Đang tạm ngừng' }}</x-ui.badge>
                        </div>

                        @if($provider->slug === 'youtube')
                            <div class="mt-4 rounded-xl border p-3 text-sm {{ ($youtubeCredentialCount ?? 0) > 0 ? 'border-emerald-200 bg-emerald-50 text-emerald-900' : ($hasEnvYoutubeKey ? 'border-blue-200 bg-blue-50 text-blue-900' : 'border-amber-200 bg-amber-50 text-amber-900') }}">
                                @if(($youtubeCredentialCount ?? 0) > 0)
                                    Credential pool: <strong>{{ $youtubeCredentialCount }} key trong Admin DB (mã hóa)</strong>.
                                @elseif($hasEnvYoutubeKey)
                                    Credential: <strong>.env fallback</strong>. Thêm pool bên dưới để chuyển authority sang Admin DB.
                                @else
                                    Credential: <strong>chưa cấu hình</strong>.
                                @endif
                            </div>
                        @endif

                        <form method="POST" action="{{ route('admin.providers.configuration.update', $provider) }}" class="mt-5 space-y-4">
                            @csrf
                            @if($provider->slug === 'musicbrainz')
                                <label class="block text-sm font-semibold">MusicBrainz User-Agent
                                    <input name="musicbrainz_user_agent" value="{{ old('musicbrainz_user_agent', (string) ($configuration['user_agent'] ?? $musicBrainzFallback)) }}" class="mt-1.5 w-full rounded-xl border border-slate-300 px-3 py-2.5" placeholder="SongChartWeb/1.0 (contact@your-domain.com)">
                                </label>
                                <p class="text-xs leading-5 text-slate-500">MusicBrainz không cần API key. Dùng email liên hệ thật; rate limit và timeout do policy hệ thống quản lý.</p>
                            @else
                                <label class="block text-sm font-semibold">YouTube Data API credential pool
                                    <textarea name="youtube_api_keys" rows="5" autocomplete="off" class="mt-1.5 w-full rounded-xl border border-slate-300 px-3 py-2.5 font-mono text-sm" placeholder="Một API key mỗi dòng. Để trống nếu không muốn thay đổi pool hiện tại."></textarea>
                                </label>
                                <p class="text-xs leading-5 text-slate-500">Nhập một hoặc nhiều key, mỗi dòng một key. Khi có giá trị, danh sách mới sẽ thay thế pool hiện tại. Secret được mã hóa, không đọc ngược ra UI. Resolver chỉ chọn key đang enabled và không cooldown; quota guard của YouTube vẫn là authority và rotation không được dùng để né quota/ToS.</p>
                            @endif

                            <label class="block text-sm font-semibold">Trạng thái vận hành
                                <select name="provider_operational_state" class="mt-1.5 w-full rounded-xl border border-slate-300 px-3 py-2.5">
                                    <option value="enabled" @selected(old('provider_operational_state', $provider->is_enabled ? 'enabled' : 'disabled') === 'enabled')>Bật provider sau khi lưu</option>
                                    <option value="disabled" @selected(old('provider_operational_state', $provider->is_enabled ? 'enabled' : 'disabled') === 'disabled')>Tạm ngừng provider</option>
                                </select>
                            </label>

                            <label class="block text-sm font-semibold">Lý do thay đổi
                                <input name="rationale" required minlength="10" maxlength="2000" class="mt-1.5 w-full rounded-xl border border-slate-300 px-3 py-2.5" placeholder="Ví dụ: Cấu hình và kích hoạt tích hợp cho môi trường demo">
                            </label>
                            <input type="hidden" name="idempotency_key" value="{{ (string) str()->uuid() }}">
                            <div class="flex flex-wrap items-center gap-3">
                                <button class="rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white">Lưu cấu hình & trạng thái</button>
                                <a href="{{ route('admin.providers.show', $provider) }}" class="text-sm font-semibold underline">Kiểm tra provider</a>
                            </div>
                        </form>
                    </div>
                </x-ui.card>
            @empty
                <x-ui.card><div class="p-5"><h3 class="font-bold">Chưa có integration khả dụng</h3><p class="mt-2 text-sm text-slate-600">Provider Registry chưa có MusicBrainz hoặc YouTube. Chạy bootstrap provider registry trước khi cấu hình.</p></div></x-ui.card>
            @endforelse
        </div>
        <div class="mt-4 rounded-xl border border-slate-200 bg-slate-50 p-4 text-sm leading-6 text-slate-600">Các tích hợp chưa có adapter vận hành hoàn chỉnh sẽ chưa mở form credential. Khi adapter được triển khai, credential phải đi vào khu Thiết lập hệ thống này. Provider có nhiều credential dùng credential pool có policy riêng; không dùng rotation để vượt quota hoặc điều khoản của nhà cung cấp.</div>
    @else
        <x-ui.card><div class="p-5"><h3 class="font-bold">API & tích hợp</h3><p class="mt-2 text-sm leading-6 text-slate-600">Tài khoản hiện tại không có quyền <code>manage-providers</code>. Super Admin hoặc Provider Manager mới có thể thay đổi credential và trạng thái tích hợp.</p></div></x-ui.card>
    @endcan
</section>

<section class="mt-8" aria-labelledby="product-signals-heading">
    <div class="mb-4">
        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Product signals · {{ $productSignals['window_days'] }} ngày</p>
        <h2 id="product-signals-heading" class="mt-1 text-xl font-bold">Nhu cầu & chất lượng tìm kiếm</h2>
        <p class="mt-1 max-w-3xl text-sm leading-6 text-slate-500">Chỉ dùng aggregate đã được phê duyệt. Không có query text, user id, session id hoặc fingerprint.</p>
    </div>
    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4" data-admin-section="product-signals">
        <x-ui.card><p class="text-sm text-slate-500">Lượt tìm kiếm</p><p class="mt-2 text-xl font-bold">{{ number_format($productSignals['search_count']) }}</p></x-ui.card>
        <x-ui.card><p class="text-sm text-slate-500">Không có kết quả</p><p class="mt-2 text-xl font-bold">{{ number_format($productSignals['zero_result_count']) }}</p></x-ui.card>
        <x-ui.card><p class="text-sm text-slate-500">Zero-result rate</p><p class="mt-2 text-xl font-bold">{{ number_format($productSignals['zero_result_rate'] * 100, 1) }}%</p></x-ui.card>
        <x-ui.card><p class="text-sm text-slate-500">Kết quả TB / search</p><p class="mt-2 text-xl font-bold">{{ number_format($productSignals['average_results_per_search'], 2) }}</p></x-ui.card>
    </div>
    <div class="mt-4 rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm leading-6 text-amber-950" data-retention-status="{{ $productSignals['retention_status'] }}">
        <strong>Retention: {{ $productSignals['retention_status'] }}.</strong> {{ $productSignals['retention_reason'] }}
    </div>
</section>

<section class="mt-8" aria-labelledby="system-health-heading">
    <div class="mb-4"><h2 id="system-health-heading" class="text-xl font-bold">Sức khỏe hệ thống</h2><p class="mt-1 text-sm text-slate-500">Thông tin runtime read-only phục vụ chẩn đoán vận hành.</p></div>
    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3" data-admin-section="system-health">@foreach($checks as $check)<x-ui.card><p class="text-sm text-slate-500">{{ $check['label'] }}</p><p class="mt-2 text-xl font-bold">{{ $check['value'] }}</p><p class="mt-2 text-xs uppercase tracking-wide text-slate-400">{{ $check['state'] }}</p></x-ui.card>@endforeach</div>
</section>
@endsection
