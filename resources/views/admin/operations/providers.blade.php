@extends('layouts.admin')
@section('content')
<x-admin.page-header :title="$title" :description="$description" />
@if(session('status'))<div class="mt-4 rounded-xl border border-emerald-200 bg-emerald-50 p-3 text-sm text-emerald-900">{{ session('status') }}</div>@endif
@include('admin.operations._metrics')

@can('manage-providers')
<section class="mt-6" aria-labelledby="provider-setup-heading">
    <div class="mb-3 flex flex-wrap items-end justify-between gap-3">
        <div>
            <h2 id="provider-setup-heading" class="text-lg font-bold">Cấu hình nguồn dữ liệu & API</h2>
            <p class="mt-1 max-w-3xl text-sm text-slate-500">Thiết lập vận hành ngay trong Admin. Cấu hình lưu trong database sẽ ưu tiên hơn <code>.env</code>; <code>.env</code> chỉ là fallback/bootstrap khi chưa có override trong Admin. Secret được mã hóa và không bao giờ hiển thị lại.</p>
        </div>
        <a href="{{ route('admin.imports.preview') }}" class="rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white">Đi tới Nhập dữ liệu</a>
    </div>

    <div class="mb-4 rounded-xl border border-blue-200 bg-blue-50 p-4 text-sm leading-6 text-blue-950">
        <strong>Không cần nhớ biến môi trường.</strong> Hãy cấu hình provider ở đây trước. Chỉ các giá trị hạ tầng cần trước khi Laravel khởi động — database, Redis, APP_KEY, APP_URL — mới nên ở <code>.env</code>.
    </div>

    <div class="grid gap-4 lg:grid-cols-2">
        @foreach($providers as $provider)
            @if(in_array($provider->slug, ['musicbrainz', 'youtube'], true))
                @php($configuration = is_array($provider->configuration) ? $provider->configuration : [])
                @php($secretKeys = is_array($configuration['_secrets'] ?? null) ? array_keys($configuration['_secrets']) : [])
                @php($hasAdminOverride = collect(array_keys($configuration))->reject(fn ($key) => $key === '_secrets')->isNotEmpty() || $secretKeys !== [])
                @php($musicBrainzFallback = (string) config('songchart.providers.musicbrainz.user_agent', 'SongChartWeb/1.0 (contact@example.com)'))
                @php($youtubeHasEnvFallback = (string) config('songchart.providers.youtube.api_key', '') !== '')
                <x-ui.card>
                    <div class="p-5">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <h3 class="font-bold text-slate-900">{{ $provider->name }}</h3>
                                <p class="mt-1 text-sm text-slate-500">{{ $provider->slug === 'musicbrainz' ? 'Nguồn metadata canonical chính cho Artist, Release, Recording và Work.' : 'Nguồn video/điểm đến media; cần YouTube Data API key.' }}</p>
                            </div>
                            <div class="flex flex-col items-end gap-2">
                                <x-ui.badge :variant="$provider->is_enabled ? 'success' : 'warning'">{{ $provider->is_enabled ? 'Đang bật' : 'Đang tắt' }}</x-ui.badge>
                                <span class="text-xs text-slate-500">{{ $hasAdminOverride ? 'Nguồn cấu hình: Admin DB' : 'Nguồn cấu hình: fallback' }}</span>
                            </div>
                        </div>

                        <form method="POST" action="{{ route('admin.providers.configuration.update', $provider) }}" class="mt-5 space-y-4">
                            @csrf
                            @if($provider->slug === 'musicbrainz')
                                <label class="block text-sm font-semibold text-slate-800">Thông tin nhận diện ứng dụng
                                    <input name="musicbrainz_user_agent" value="{{ old('musicbrainz_user_agent', (string) ($configuration['user_agent'] ?? $musicBrainzFallback)) }}" class="mt-1.5 w-full rounded-xl border border-slate-300 px-3 py-2.5" placeholder="SongChartWeb/1.0 (contact@your-domain.com)">
                                </label>
                                <div class="rounded-lg bg-slate-50 p-3 text-xs leading-5 text-slate-600">
                                    MusicBrainz không cần API key. Giá trị khuyến nghị có dạng <code>SongChartWeb/1.0 (contact@your-domain.com)</code>. Chỉ cần thay email liên hệ bằng địa chỉ thật; các timeout/rate-limit an toàn đã có default trong hệ thống và không cần SA nhập thủ công.
                                </div>
                            @else
                                <label class="block text-sm font-semibold text-slate-800">YouTube Data API key
                                    <input type="password" name="youtube_api_key" autocomplete="new-password" class="mt-1.5 w-full rounded-xl border border-slate-300 px-3 py-2.5" placeholder="{{ in_array('api_key', $secretKeys, true) ? 'Đã cấu hình trong Admin — để trống nếu không đổi' : ($youtubeHasEnvFallback ? 'Đang dùng .env fallback — nhập để chuyển sang Admin DB' : 'Nhập API key') }}">
                                </label>
                                @if(in_array('api_key', $secretKeys, true))
                                    <p class="text-xs leading-5 text-emerald-700">API key đã được lưu mã hóa trong Admin DB và đang override <code>.env</code>.</p>
                                @elseif($youtubeHasEnvFallback)
                                    <p class="text-xs leading-5 text-blue-700">Hiện đang dùng API key từ <code>.env</code> fallback. Nhập key ở đây để chuyển quyền quản lý sang Admin UI.</p>
                                @else
                                    <p class="text-xs leading-5 text-amber-700">Chưa có API key. Tạo key trong Google Cloud Console rồi dán vào đây; hệ thống sẽ mã hóa trước khi lưu.</p>
                                @endif
                            @endif

                            <label class="block text-sm font-semibold text-slate-800">Lý do thay đổi
                                <input name="rationale" required minlength="10" maxlength="2000" class="mt-1.5 w-full rounded-xl border border-slate-300 px-3 py-2.5" placeholder="Ví dụ: Thiết lập nguồn dữ liệu cho demo vận hành">
                            </label>
                            <input type="hidden" name="idempotency_key" value="{{ (string) str()->uuid() }}">
                            <div class="flex flex-wrap items-center gap-3">
                                <button class="rounded-xl border border-slate-300 px-4 py-2.5 font-semibold text-slate-800">Lưu cấu hình</button>
                                <a class="text-sm font-semibold underline" href="{{ route('admin.providers.show', $provider) }}">Trạng thái, bật/tắt và test nguồn</a>
                            </div>
                        </form>
                    </div>
                </x-ui.card>
            @endif
        @endforeach
    </div>
    <div class="mt-4 rounded-xl border border-slate-200 bg-slate-50 p-4 text-sm text-slate-600">Spotify, Apple Music, SoundCloud và Wikidata vẫn ở registry nhưng chưa có adapter nhập dữ liệu đầy đủ. Khi adapter được triển khai, cấu hình vận hành của chúng cũng nên đi qua cùng Admin DB authority thay vì bắt SA chỉnh <code>.env</code> thủ công.</div>
</section>
@else
<section class="mt-6"><x-ui.card><div class="p-5"><h2 class="font-bold">Nguồn dữ liệu & API</h2><p class="mt-2 text-sm leading-6 text-slate-600">Tài khoản hiện tại có thể vào Admin nhưng không có quyền <code>manage-providers</code>, vì vậy cấu hình nguồn dữ liệu đang ở chế độ chỉ đọc. Super Admin và Provider Manager có thể quản lý phần này.</p></div></x-ui.card></section>
@endcan

<x-ui.card class="mt-6"><form method="GET" class="grid gap-3 p-4 md:grid-cols-4"><input class="rounded border p-2" name="q" value="{{ $filters['term'] }}" placeholder="Tên nguồn dữ liệu"><select class="rounded border p-2" name="status"><option value="">Mọi trạng thái</option>@foreach(\App\Domain\Providers\Enums\ProviderStatus::cases() as $case)<option value="{{ $case->value }}" @selected($filters['status']===$case->value)>{{ $presentation->providerStatus($case, true)['label'] }}</option>@endforeach</select><select class="rounded border p-2" name="enabled"><option value="">Hoạt động và tạm ngừng</option><option value="1" @selected($filters['enabled']==='1')>Đang sử dụng</option><option value="0" @selected($filters['enabled']==='0')>Đang tạm ngừng</option></select><button class="rounded border px-4 py-2" type="submit">Lọc</button></form></x-ui.card>
<section class="mt-6" data-admin-section="providers"><x-ui.card><div class="overflow-x-auto"><table class="min-w-full text-left text-sm"><thead><tr class="border-b"><th class="p-3">Nguồn dữ liệu</th><th class="p-3">Tình trạng</th><th class="p-3">Khả năng</th><th class="p-3">Rà soát chính sách</th><th class="p-3"></th></tr></thead><tbody class="divide-y">@forelse($providers as $provider)@php($state=$presentation->providerStatus($provider->status,$provider->is_enabled))<tr><td class="p-3"><strong>{{ $provider->name }}</strong><div class="text-xs text-slate-500">{{ $provider->category }}</div></td><td class="p-3"><x-ui.badge :variant="$state['tone']">{{ $state['label'] }}</x-ui.badge></td><td class="p-3">{{ $provider->capabilities_count }}</td><td class="p-3">{{ $provider->policy_reviewed_at?->format('d/m/Y') ?? 'Chưa ghi nhận' }}</td><td class="p-3"><a class="font-semibold underline" href="{{ route('admin.providers.show',$provider) }}">Xem chi tiết</a></td></tr>@empty<tr><td colspan="5" class="p-8 text-center text-slate-500">Chưa có nguồn dữ liệu nào được đăng ký.</td></tr>@endforelse</tbody></table></div><div class="p-4">{{ $providers->links() }}</div></x-ui.card></section>
@endsection
