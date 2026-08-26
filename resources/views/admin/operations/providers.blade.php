@extends('layouts.admin')
@section('content')
<x-admin.page-header :title="$title" :description="$description" />
@if(session('status'))<div class="mt-4 rounded-xl border border-emerald-200 bg-emerald-50 p-3 text-sm text-emerald-900">{{ session('status') }}</div>@endif
@include('admin.operations._metrics')

@can('manage-providers')
<section class="mt-6" aria-labelledby="provider-setup-heading">
    <div class="mb-3 flex flex-wrap items-end justify-between gap-3">
        <div><h2 id="provider-setup-heading" class="text-lg font-bold">Thiết lập nguồn dữ liệu</h2><p class="mt-1 text-sm text-slate-500">Cấu hình các thông tin cần thiết ngay trong Admin. Secret được mã hóa và không hiển thị lại sau khi lưu.</p></div>
        <a href="{{ route('admin.imports.preview') }}" class="rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white">Đi tới Nhập dữ liệu</a>
    </div>
    <div class="grid gap-4 lg:grid-cols-2">
        @foreach($providers as $provider)
            @if(in_array($provider->slug, ['musicbrainz', 'youtube'], true))
                @php($configuration = is_array($provider->configuration) ? $provider->configuration : [])
                @php($secretKeys = is_array($configuration['_secrets'] ?? null) ? array_keys($configuration['_secrets']) : [])
                <x-ui.card>
                    <div class="p-5">
                        <div class="flex items-start justify-between gap-4">
                            <div><h3 class="font-bold text-slate-900">{{ $provider->name }}</h3><p class="mt-1 text-sm text-slate-500">{{ $provider->slug === 'musicbrainz' ? 'Tìm nghệ sĩ, nhóm nhạc và bản ghi âm.' : 'Tìm và xác minh video/điểm đến media.' }}</p></div>
                            <x-ui.badge :variant="$provider->is_enabled ? 'success' : 'warning'">{{ $provider->is_enabled ? 'Đang bật' : 'Đang tắt' }}</x-ui.badge>
                        </div>
                        <form method="POST" action="{{ route('admin.providers.configuration.update', $provider) }}" class="mt-5 space-y-4">
                            @csrf
                            @if($provider->slug === 'musicbrainz')
                                <label class="block text-sm font-semibold text-slate-800">Thông tin nhận diện ứng dụng
                                    <input name="musicbrainz_user_agent" value="{{ old('musicbrainz_user_agent', (string) ($configuration['user_agent'] ?? '')) }}" class="mt-1.5 w-full rounded-xl border border-slate-300 px-3 py-2.5" placeholder="SongChartWeb/1.0 (contact@your-domain.com)">
                                </label>
                                <p class="text-xs leading-5 text-slate-500">MusicBrainz không cần API key nhưng yêu cầu User-Agent có thông tin liên hệ thật.</p>
                            @else
                                <label class="block text-sm font-semibold text-slate-800">YouTube Data API key
                                    <input type="password" name="youtube_api_key" autocomplete="new-password" class="mt-1.5 w-full rounded-xl border border-slate-300 px-3 py-2.5" placeholder="{{ in_array('api_key', $secretKeys, true) ? 'Đã cấu hình — để trống nếu không đổi' : 'Nhập API key' }}">
                                </label>
                                <p class="text-xs leading-5 {{ in_array('api_key', $secretKeys, true) ? 'text-emerald-700' : 'text-amber-700' }}">{{ in_array('api_key', $secretKeys, true) ? 'API key đã được lưu mã hóa.' : 'Chưa có API key trong cấu hình Admin.' }}</p>
                            @endif
                            <label class="block text-sm font-semibold text-slate-800">Lý do thay đổi
                                <input name="rationale" required minlength="10" maxlength="2000" class="mt-1.5 w-full rounded-xl border border-slate-300 px-3 py-2.5" placeholder="Ví dụ: Thiết lập nguồn cho môi trường vận hành">
                            </label>
                            <input type="hidden" name="idempotency_key" value="{{ (string) str()->uuid() }}">
                            <button class="rounded-xl border border-slate-300 px-4 py-2.5 font-semibold text-slate-800">Lưu thiết lập</button>
                        </form>
                    </div>
                </x-ui.card>
            @endif
        @endforeach
    </div>
    <div class="mt-4 rounded-xl border border-slate-200 bg-slate-50 p-4 text-sm text-slate-600">Spotify, Apple Music, SoundCloud và Wikidata vẫn xuất hiện trong registry nhưng chưa có cấu hình nhập dữ liệu đầy đủ ở Stage 18.1. UI sẽ chỉ mở khi adapter tương ứng được triển khai và kiểm chứng.</div>
</section>
@endcan

<x-ui.card class="mt-6"><form method="GET" class="grid gap-3 p-4 md:grid-cols-4"><input class="rounded border p-2" name="q" value="{{ $filters['term'] }}" placeholder="Tên nguồn dữ liệu"><select class="rounded border p-2" name="status"><option value="">Mọi trạng thái</option>@foreach(\App\Domain\Providers\Enums\ProviderStatus::cases() as $case)<option value="{{ $case->value }}" @selected($filters['status']===$case->value)>{{ $presentation->providerStatus($case, true)['label'] }}</option>@endforeach</select><select class="rounded border p-2" name="enabled"><option value="">Hoạt động và tạm ngừng</option><option value="1" @selected($filters['enabled']==='1')>Đang sử dụng</option><option value="0" @selected($filters['enabled']==='0')>Đang tạm ngừng</option></select><button class="rounded border px-4 py-2" type="submit">Lọc</button></form></x-ui.card>
<section class="mt-6" data-admin-section="providers"><x-ui.card><div class="overflow-x-auto"><table class="min-w-full text-left text-sm"><thead><tr class="border-b"><th class="p-3">Nguồn dữ liệu</th><th class="p-3">Tình trạng</th><th class="p-3">Khả năng</th><th class="p-3">Rà soát chính sách</th><th class="p-3"></th></tr></thead><tbody class="divide-y">@forelse($providers as $provider)@php($state=$presentation->providerStatus($provider->status,$provider->is_enabled))<tr><td class="p-3"><strong>{{ $provider->name }}</strong><div class="text-xs text-slate-500">{{ $provider->category }}</div></td><td class="p-3"><x-ui.badge :variant="$state['tone']">{{ $state['label'] }}</x-ui.badge></td><td class="p-3">{{ $provider->capabilities_count }}</td><td class="p-3">{{ $provider->policy_reviewed_at?->format('d/m/Y') ?? 'Chưa ghi nhận' }}</td><td class="p-3"><a class="font-semibold underline" href="{{ route('admin.providers.show',$provider) }}">Xem chi tiết</a></td></tr>@empty<tr><td colspan="5" class="p-8 text-center text-slate-500">Chưa có nguồn dữ liệu nào được đăng ký.</td></tr>@endforelse</tbody></table></div><div class="p-4">{{ $providers->links() }}</div></x-ui.card></section>
@endsection
