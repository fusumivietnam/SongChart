@extends('layouts.admin')
@section('content')
<x-admin.page-header title="Xem xét thay đổi dữ liệu" description="Kiểm tra đề xuất, nguồn cung cấp và tác động trước khi quyết định." />

<x-ui.card class="mt-6">
    <div class="p-5">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <p class="text-sm text-slate-500">Loại dữ liệu</p>
                <h2 class="mt-1 text-lg font-semibold">{{ $admission->entity_type->label() }}</h2>
            </div>
            <span class="inline-flex w-fit rounded border px-2 py-1 text-xs font-semibold">
                {{ ['pending' => 'Cần duyệt', 'applied' => 'Đã chấp nhận', 'rejected' => 'Đã từ chối'][$admission->status->value] ?? $admission->status->value }}
            </span>
        </div>

        <dl class="mt-5 grid gap-5 md:grid-cols-2">
            <div>
                <dt class="text-sm font-semibold">Nội dung được đề xuất</dt>
                <dd class="mt-1 text-sm text-slate-600">{{ str($admission->field_name)->replace('_', ' ')->headline() }}</dd>
            </div>
            <div>
                <dt class="text-sm font-semibold">Nguồn</dt>
                <dd class="mt-1 text-sm text-slate-600">{{ $admission->assertion?->source?->name ?? 'Không xác định' }}</dd>
            </div>
            <div class="md:col-span-2">
                <dt class="text-sm font-semibold">Giá trị đề xuất</dt>
                <dd class="mt-2 rounded border bg-slate-50 p-3 text-sm break-words">{{ is_scalar($admission->assertion?->value) || $admission->assertion?->value === null ? (string) ($admission->assertion?->value ?? 'Không có giá trị') : json_encode($admission->assertion?->value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) }}</dd>
            </div>
            @if($admission->decision_reason)
                <div class="md:col-span-2">
                    <dt class="text-sm font-semibold">Lý do quyết định</dt>
                    <dd class="mt-1 text-sm text-slate-600">{{ $admission->decision_reason }}</dd>
                </div>
            @endif
            @if($admission->reviewer)
                <div>
                    <dt class="text-sm font-semibold">Người duyệt</dt>
                    <dd class="mt-1 text-sm text-slate-600">{{ $admission->reviewer->email }}</dd>
                </div>
            @endif
        </dl>

        <details class="mt-5 border-t pt-4 text-sm">
            <summary class="min-h-11 cursor-pointer py-2 font-semibold">Chi tiết kỹ thuật</summary>
            <dl class="mt-3 grid gap-2 text-slate-600 md:grid-cols-2">
                <div><dt class="font-medium">ID đối tượng</dt><dd class="break-all font-mono text-xs">{{ $admission->entity_id }}</dd></div>
                <div><dt class="font-medium">Trường dữ liệu</dt><dd class="break-all font-mono text-xs">{{ $admission->field_name }}</dd></div>
                <div><dt class="font-medium">ID đề xuất nguồn</dt><dd class="break-all font-mono text-xs">{{ $admission->metadata_assertion_id }}</dd></div>
                <div><dt class="font-medium">Trạng thái hệ thống</dt><dd class="font-mono text-xs">{{ $admission->status->value }}</dd></div>
            </dl>
        </details>
    </div>
</x-ui.card>

@if($admission->status->value === 'pending')
<x-ui.card class="mt-6">
    <div class="p-5">
        <h2 class="font-semibold">Bạn muốn xử lý đề xuất này thế nào?</h2>
        <p class="mt-1 text-sm text-slate-500">Quyết định sẽ được ghi lại. Hãy nêu lý do đủ rõ để người khác có thể hiểu và kiểm tra lại sau này.</p>

        <form method="post" action="{{ route('admin.canonical-admissions.decide', $admission) }}" class="mt-5 space-y-4">
            @csrf
            <label class="block text-sm font-semibold">
                Lý do quyết định
                <textarea name="rationale" required minlength="10" maxlength="2000" class="mt-2 w-full rounded border-slate-300 bg-transparent" rows="5" aria-describedby="decision-rationale-help">{{ old('rationale') }}</textarea>
            </label>
            <p id="decision-rationale-help" class="text-xs text-slate-500">Ví dụ: nguồn đáng tin cậy, giá trị phù hợp với dữ liệu hiện có, hoặc có mâu thuẫn cần từ chối.</p>

            <div class="flex flex-col gap-3 sm:flex-row">
                <button name="action" value="apply" class="min-h-11 rounded border px-4 py-2 font-semibold">Chấp nhận và cập nhật dữ liệu</button>
                <button name="action" value="reject" class="min-h-11 rounded border px-4 py-2 font-semibold">Từ chối đề xuất</button>
            </div>
        </form>
    </div>
</x-ui.card>
@endif
@endsection
