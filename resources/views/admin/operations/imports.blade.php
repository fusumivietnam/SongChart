@extends('layouts.admin')
@php($activeAdminNav = 'imports')
@section('content')
<x-admin.page-header :title="$title" :description="$description" />
<div class="mt-6 rounded-2xl border border-indigo-100 bg-indigo-50 p-5 md:flex md:items-center md:justify-between md:gap-6">
    <div>
        <div class="text-sm font-bold text-indigo-950">Nhập dữ liệu mới</div>
        <p class="mt-1 max-w-2xl text-sm text-indigo-800">Bắt đầu bằng bước xem trước để SongChart kiểm tra định danh, thuộc tính và quan hệ. Bạn sẽ thấy kế hoạch nhập trước khi tạo tác vụ và dữ liệu chuẩn không bị ghi trực tiếp.</p>
    </div>
    <a href="{{ route('admin.imports.preview') }}" class="mt-4 inline-flex rounded-xl bg-indigo-600 px-4 py-2.5 font-semibold text-white hover:bg-indigo-700 md:mt-0">Bắt đầu nhập dữ liệu</a>
</div>
@include('admin.operations._metrics')
<x-ui.card class="mt-6"><form method="GET" class="grid gap-3 p-4 md:grid-cols-4"><select class="rounded border p-2" name="provider"><option value="">Mọi nguồn dữ liệu</option>@foreach($providers as $provider)<option value="{{ $provider->id }}" @selected($filters['providerId']===$provider->id)>{{ $provider->name }}</option>@endforeach</select><input class="rounded border p-2" name="operation" value="{{ $filters['operation'] }}" placeholder="Loại tác vụ"><input class="rounded border p-2" name="status" value="{{ $filters['status'] }}" placeholder="Trạng thái"><button class="rounded border px-4 py-2">Lọc</button></form></x-ui.card>
<section class="mt-6" data-admin-section="imports"><x-ui.card><div class="overflow-x-auto"><table class="min-w-full text-left text-sm"><thead><tr class="border-b"><th class="p-3">Tác vụ</th><th class="p-3">Nguồn</th><th class="p-3">Tình trạng</th><th class="p-3">Số lần thử</th><th class="p-3">Bắt đầu</th><th class="p-3"></th></tr></thead><tbody class="divide-y">@forelse($runs as $run)@php($state=$presentation->importStatus($run->status))<tr><td class="p-3 font-semibold">{{ $presentation->operation($run->operation) }}</td><td class="p-3">{{ $run->provider?->name ?? 'Không xác định' }}</td><td class="p-3"><x-ui.badge :variant="$state['tone']">{{ $state['label'] }}</x-ui.badge>@if($run->error_summary)<div class="mt-1 max-w-xs truncate text-xs text-slate-500">{{ $run->error_summary }}</div>@endif</td><td class="p-3">{{ $run->attempts }}</td><td class="p-3">{{ $run->started_at?->format('d/m/Y H:i') ?? $run->created_at?->format('d/m/Y H:i') }}</td><td class="p-3"><a class="font-semibold underline" href="{{ route('admin.imports.show',$run) }}">Xem tiến trình</a></td></tr>@empty<tr><td colspan="6" class="p-8 text-center text-slate-500">Chưa có tác vụ dữ liệu nào.</td></tr>@endforelse</tbody></table></div><div class="p-4">{{ $runs->links() }}</div></x-ui.card></section>
@endsection
