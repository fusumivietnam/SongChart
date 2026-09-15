@extends('layouts.admin')
@section('content')
<x-admin.page-header title="Xem xét thay đổi dữ liệu" description="Kiểm tra đề xuất, nguồn cung cấp và tác động trước khi quyết định." />

<x-ui.card class="mt-6">
    <div class="p-5">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <p class="text-sm text-[var(--admin-text-secondary)]">Loại dữ liệu</p>
                <h2 class="mt-1 text-lg font-semibold">{{ $admission->entity_type->label() }}</h2>
            </div>
            <span class="inline-flex w-fit rounded border border-[var(--admin-border)] px-2 py-1 text-xs font-semibold">
                {{ ['pending' => 'Cần duyệt', 'applied' => 'Đã chấp nhận', 'rejected' => 'Đã từ chối'][$admission->status->value] ?? $admission->status->value }}
            </span>
        </div>

        <dl class="mt-5 grid gap-5 md:grid-cols-2">
            <div>
                <dt class="text-sm font-semibold">Nội dung được đề xuất</dt>
                <dd class="mt-1 text-sm text-[var(--admin-text-secondary)]">{{ str($admission->field_name)->replace('_', ' ')->headline() }}</dd>
            </div>
            <div>
                <dt class="text-sm font-semibold">Nguồn</dt>
                <dd class="mt-1 text-sm text-[var(--admin-text-secondary)]">{{ $admission->assertion?->source?->name ?? 'Không xác định' }}</dd>
            </div>
            <div class="md:col-span-2">
                <dt class="text-sm font-semibold">Giá trị đề xuất</dt>
                <dd class="mt-2 break-words rounded border border-[var(--admin-border)] bg-[var(--admin-bg-subtle)] p-3 text-sm">@include('admin.canonical-admissions.partials.value', ['value' => $admission->assertion?->value])</dd>
            </div>
            @if($admission->decision_reason)
                <div class="md:col-span-2">
                    <dt class="text-sm font-semibold">Lý do quyết định</dt>
                    <dd class="mt-1 text-sm text-[var(--admin-text-secondary)]">{{ $admission->decision_reason }}</dd>
                </div>
            @endif
            @if($admission->reviewer)
                <div>
                    <dt class="text-sm font-semibold">Người duyệt</dt>
                    <dd class="mt-1 text-sm text-[var(--admin-text-secondary)]">{{ $admission->reviewer->email }}</dd>
                </div>
            @endif
        </dl>

        <details class="mt-5 border-t border-[var(--admin-border)] pt-4 text-sm">
            <summary class="min-h-11 cursor-pointer py-2 font-semibold">Chi tiết kỹ thuật</summary>
            <dl class="mt-3 grid gap-2 text-[var(--admin-text-secondary)] md:grid-cols-2">
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
        <p class="mt-1 text-sm text-[var(--admin-text-secondary)]">Quyết định chỉ áp dụng cho đề xuất đang xem và sẽ được ghi lại để có thể kiểm tra về sau.</p>

        <div class="mt-5 grid gap-3 md:grid-cols-2" aria-label="Tác động của từng quyết định">
            <div class="rounded border border-[var(--admin-border)] p-4" id="apply-consequence">
                <h3 class="font-semibold">Nếu chấp nhận</h3>
                <p class="mt-1 text-sm text-[var(--admin-text-secondary)]">Giá trị đề xuất này sẽ được cập nhật vào dữ liệu SongChart thông qua quy trình duyệt hiện có.</p>
            </div>
            <div class="rounded border border-[var(--admin-border)] p-4" id="reject-consequence">
                <h3 class="font-semibold">Nếu từ chối</h3>
                <p class="mt-1 text-sm text-[var(--admin-text-secondary)]">Dữ liệu SongChart không thay đổi. Đề xuất và lý do từ chối vẫn được giữ lại trong lịch sử duyệt.</p>
            </div>
        </div>

        <form method="post" action="{{ route('admin.canonical-admissions.decide', $admission) }}" class="mt-5 space-y-4">
            @csrf
            <label class="block text-sm font-semibold" for="decision-rationale">
                Lý do quyết định
            </label>
            <textarea id="decision-rationale" name="rationale" required minlength="10" maxlength="2000" class="w-full rounded border border-[var(--admin-border-strong)] bg-transparent focus:border-[var(--admin-focus)] focus:outline-none focus:ring-2 focus:ring-[var(--admin-focus)]" rows="5" aria-describedby="decision-rationale-help decision-rationale-error">{{ old('rationale') }}</textarea>
            <p id="decision-rationale-help" class="text-xs text-[var(--admin-text-secondary)]">Nêu ngắn gọn căn cứ để người khác có thể hiểu quyết định sau này, ví dụ độ tin cậy của nguồn hoặc mâu thuẫn với dữ liệu hiện có.</p>
            @error('rationale')
                <p id="decision-rationale-error" class="text-sm font-medium text-[var(--admin-danger)]" role="alert">{{ $message }}</p>
            @else
                <span id="decision-rationale-error" class="sr-only">Lý do phải có ít nhất 10 ký tự.</span>
            @enderror

            <fieldset>
                <legend class="text-sm font-semibold">Chọn quyết định</legend>
                <div class="mt-3 grid gap-3 sm:grid-cols-2">
                    <button name="action" value="apply" class="min-h-11 w-full rounded border border-[var(--admin-border)] px-4 py-3 text-left font-semibold" aria-describedby="apply-consequence">
                        Chấp nhận và cập nhật dữ liệu
                    </button>
                    <button name="action" value="reject" class="min-h-11 w-full rounded border border-[var(--admin-border)] px-4 py-3 text-left font-semibold" aria-describedby="reject-consequence">
                        Từ chối đề xuất
                    </button>
                </div>
            </fieldset>
        </form>
    </div>
</x-ui.card>
@endif
@endsection