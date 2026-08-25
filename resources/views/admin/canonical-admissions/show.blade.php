@extends('layouts.admin')
@section('content')
<x-admin.page-header title="Canonical admission review" description="Quyết định này được audit và chỉ apply một lần." />

<x-ui.card class="mt-6"><div class="p-5 space-y-3 text-sm">
    <div><span class="font-semibold">Entity:</span> {{ $admission->entity_type->label() }} · <code>{{ $admission->entity_id }}</code></div>
    <div><span class="font-semibold">Field:</span> <code>{{ $admission->field_name }}</code></div>
    <div><span class="font-semibold">Source:</span> {{ $admission->assertion?->source?->name ?? 'Không xác định' }}</div>
    <div><span class="font-semibold">Assertion:</span> <code>{{ $admission->metadata_assertion_id }}</code></div>
    <div><span class="font-semibold">Giá trị evidence:</span> <code>{{ json_encode($admission->assertion?->value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) }}</code></div>
    <div><span class="font-semibold">Trạng thái:</span> {{ $admission->status->value }}</div>
    @if($admission->decision_reason)<div><span class="font-semibold">Rationale:</span> {{ $admission->decision_reason }}</div>@endif
    @if($admission->reviewer)<div><span class="font-semibold">Reviewer:</span> {{ $admission->reviewer->email }}</div>@endif
</div></x-ui.card>

@if($admission->status->value === 'pending')
<x-ui.card class="mt-6"><div class="p-5"><form method="post" action="{{ route('admin.canonical-admissions.decide', $admission) }}" class="space-y-4">@csrf
    <label class="block text-sm font-semibold">Rationale<textarea name="rationale" required minlength="10" maxlength="2000" class="mt-1 w-full rounded border-slate-300 bg-transparent" rows="5">{{ old('rationale') }}</textarea></label>
    <div class="flex gap-3"><button name="action" value="apply" class="rounded border px-4 py-2 font-semibold">Apply canonical</button><button name="action" value="reject" class="rounded border px-4 py-2 font-semibold">Reject evidence</button></div>
</form></div></x-ui.card>
@endif
@endsection
