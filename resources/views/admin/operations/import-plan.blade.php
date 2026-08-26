@extends('layouts.admin')
@php($activeAdminNav = 'import-workbench')

@section('content')
<x-admin.page-header :title="$title" :description="$description" />

<div class="mt-6 grid gap-6 xl:grid-cols-[minmax(0,1fr)_minmax(22rem,.75fr)]">
    <x-ui.card>
        <div class="p-5">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div>
                    <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">Kế hoạch nhập</div>
                    <h2 class="mt-1 text-xl font-bold text-slate-900">{{ $plan->executable ? 'Sẵn sàng tạo tác vụ nhập' : 'Chưa thể tạo tác vụ nhập' }}</h2>
                    <p class="mt-1 max-w-2xl text-sm text-slate-600">SongChart đã dựng kế hoạch từ chính payload vừa xem trước. Tạo tác vụ chỉ khởi động pipeline provider hiện có; dữ liệu chuẩn vẫn phải đi qua xác minh, identity resolution và canonical admission.</p>
                </div>
                <x-ui.badge :variant="$plan->executable ? 'success' : 'warning'">{{ $plan->executable ? 'Có thể chạy' : 'Cần kiểm tra' }}</x-ui.badge>
            </div>

            <div class="mt-5 grid grid-cols-2 gap-3 md:grid-cols-3">
                @foreach([
                    'identifiers' => 'Định danh',
                    'fields' => 'Thuộc tính',
                    'relationships' => 'Quan hệ',
                    'rich_evidence' => 'Bằng chứng mở rộng',
                    'review_items' => 'Cần xem xét',
                    'direct_canonical_mutations' => 'Ghi trực tiếp dữ liệu chuẩn',
                ] as $key => $label)
                    <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                        <div class="text-2xl font-bold text-slate-900">{{ $plan->counts[$key] ?? 0 }}</div>
                        <div class="mt-1 text-xs font-medium text-slate-600">{{ $label }}</div>
                    </div>
                @endforeach
            </div>

            <div class="mt-6 rounded-xl border border-slate-200 p-4">
                <dl class="grid gap-4 text-sm md:grid-cols-2">
                    <div><dt class="text-slate-500">Nguồn dữ liệu</dt><dd class="mt-1 font-semibold text-slate-900">{{ $plan->providerSlug }}</dd></div>
                    <div><dt class="text-slate-500">Loại thực thể</dt><dd class="mt-1 font-semibold text-slate-900">{{ $plan->entityType->value }}</dd></div>
                    <div><dt class="text-slate-500">Mã bên nguồn</dt><dd class="mt-1 break-all font-semibold text-slate-900">{{ $plan->externalId }}</dd></div>
                    <div><dt class="text-slate-500">Tác vụ sẽ tạo</dt><dd class="mt-1 font-semibold text-slate-900">{{ $plan->operation }}</dd></div>
                </dl>
            </div>

            @if($plan->reviewReasons !== [])
                <div class="mt-5 rounded-xl border border-amber-200 bg-amber-50 p-4">
                    <h3 class="font-semibold text-amber-900">Điểm cần xử lý trước</h3>
                    <ul class="mt-2 space-y-2 text-sm text-amber-900">
                        @foreach($plan->reviewReasons as $reason)<li>{{ $reason }}</li>@endforeach
                    </ul>
                </div>
            @endif

            <div class="mt-6 rounded-xl border border-blue-200 bg-blue-50 p-4 text-sm text-blue-900">
                <div class="font-semibold">Không ghi trực tiếp vào dữ liệu chuẩn</div>
                <p class="mt-1 text-blue-800">Kế hoạch này tạo một provider import run có kiểm soát. Các bằng chứng sau khi fetch vẫn đi qua normalizer, validation, identity resolution và canonical admission như pipeline hiện hành.</p>
            </div>
        </div>
    </x-ui.card>

    <div class="space-y-6">
        <x-ui.card>
            <div class="p-5">
                <h3 class="text-lg font-bold text-slate-900">Bước tiếp theo</h3>
                @if($plan->executable)
                    <p class="mt-2 text-sm text-slate-600">Xác nhận để SongChart tạo một tác vụ nhập. Hệ thống sẽ kiểm tra lại fingerprint của kế hoạch ngay trước khi dispatch.</p>
                    <form method="POST" action="{{ route('admin.imports.preview.execute') }}" class="mt-5 space-y-3">
                        @csrf
                        <input type="hidden" name="provider_slug" value="{{ $submitted['provider_slug'] }}">
                        <input type="hidden" name="entity_type" value="{{ $submitted['entity_type'] }}">
                        <input type="hidden" name="external_id" value="{{ $submitted['external_id'] }}">
                        <input type="hidden" name="payload_json" value="{{ $submitted['payload_json'] }}">
                        <input type="hidden" name="plan_fingerprint" value="{{ $plan->fingerprint }}">
                        @error('plan_fingerprint')<div class="rounded-lg border border-red-200 bg-red-50 p-3 text-sm text-red-700">{{ $message }}</div>@enderror
                        <button type="submit" class="w-full rounded-xl bg-indigo-600 px-5 py-3 font-semibold text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">Tạo tác vụ nhập</button>
                    </form>
                @else
                    <p class="mt-2 text-sm text-slate-600">Hãy quay lại preview và sửa các mục cảnh báo trước khi tạo tác vụ.</p>
                @endif
                <a href="{{ route('admin.imports.preview') }}" class="mt-3 block w-full rounded-xl border border-slate-300 px-4 py-2.5 text-center font-semibold text-slate-700">Quay lại xem trước</a>
            </div>
        </x-ui.card>

        <x-ui.card>
            <div class="p-5">
                <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">Dấu vân tay kế hoạch</div>
                <div class="mt-2 break-all font-mono text-xs text-slate-600">{{ $plan->fingerprint }}</div>
                <p class="mt-2 text-xs text-slate-500">Nếu payload hoặc kế hoạch thay đổi trước khi xác nhận, SongChart sẽ yêu cầu xem trước lại thay vì chạy với dữ liệu khác.</p>
            </div>
        </x-ui.card>
    </div>
</div>
@endsection
