@extends('layouts.admin')
@section('content')
<x-admin.page-header :title="$title" :description="$description" />
@if(session('status'))<div class="mt-4 rounded-xl border border-emerald-200 bg-emerald-50 p-3 text-sm text-emerald-900">{{ session('status') }}</div>@endif
@include('admin.operations._metrics')

<section class="mt-6" aria-labelledby="provider-config-location">
    <x-ui.card>
        <div class="flex flex-wrap items-center justify-between gap-4 p-5">
            <div>
                <h2 id="provider-config-location" class="font-bold">Cấu hình API nằm trong Thiết lập hệ thống</h2>
                <p class="mt-1 max-w-3xl text-sm leading-6 text-slate-500">Trang này tập trung vào trạng thái nguồn, capability, bật/tắt, kiểm thử và nhập dữ liệu. Credential và thông tin kết nối được quản lý tập trung tại <strong>Thiết lập hệ thống → API & tích hợp</strong>.</p>
            </div>
            @can('manage-providers')
                <a href="{{ route('admin.system.index') }}#api-integrations" class="rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white">Mở API & tích hợp</a>
            @endcan
        </div>
    </x-ui.card>
</section>

<x-ui.card class="mt-6"><form method="GET" class="grid gap-3 p-4 md:grid-cols-4"><input class="rounded border p-2" name="q" value="{{ $filters['term'] }}" placeholder="Tên nguồn dữ liệu"><select class="rounded border p-2" name="status"><option value="">Mọi trạng thái</option>@foreach(\App\Domain\Providers\Enums\ProviderStatus::cases() as $case)<option value="{{ $case->value }}" @selected($filters['status']===$case->value)>{{ $presentation->providerStatus($case, true)['label'] }}</option>@endforeach</select><select class="rounded border p-2" name="enabled"><option value="">Hoạt động và tạm ngừng</option><option value="1" @selected($filters['enabled']==='1')>Đang sử dụng</option><option value="0" @selected($filters['enabled']==='0')>Đang tạm ngừng</option></select><button class="rounded border px-4 py-2" type="submit">Lọc</button></form></x-ui.card>
<section class="mt-6" data-admin-section="providers"><x-ui.card><div class="overflow-x-auto"><table class="min-w-full text-left text-sm"><thead><tr class="border-b"><th class="p-3">Nguồn dữ liệu</th><th class="p-3">Tình trạng</th><th class="p-3">Khả năng</th><th class="p-3">Rà soát chính sách</th><th class="p-3"></th></tr></thead><tbody class="divide-y">@forelse($providers as $provider)@php($state=$presentation->providerStatus($provider->status,$provider->is_enabled))<tr><td class="p-3"><strong>{{ $provider->name }}</strong><div class="text-xs text-slate-500">{{ $provider->category }}</div></td><td class="p-3"><x-ui.badge :variant="$state['tone']">{{ $state['label'] }}</x-ui.badge></td><td class="p-3">{{ $provider->capabilities_count }}</td><td class="p-3">{{ $provider->policy_reviewed_at?->format('d/m/Y') ?? 'Chưa ghi nhận' }}</td><td class="p-3"><a class="font-semibold underline" href="{{ route('admin.providers.show',$provider) }}">Xem chi tiết</a></td></tr>@empty<tr><td colspan="5" class="p-8 text-center text-slate-500">Chưa có nguồn dữ liệu nào được đăng ký.</td></tr>@endforelse</tbody></table></div><div class="p-4">{{ $providers->links() }}</div></x-ui.card></section>
@endsection
