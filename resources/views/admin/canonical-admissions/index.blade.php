@extends('layouts.admin')
@section('content')
<x-admin.page-header title="Canonical admission" description="Rà soát evidence trước khi được phép thay đổi dữ liệu canonical." />

@if(! $available)
<x-ui.card class="mt-6">
    <div class="p-5">
        <h2 class="font-semibold">Cần cập nhật cơ sở dữ liệu phát triển</h2>
        <p class="mt-2 text-sm text-slate-600">Màn rà soát canonical admission chưa sẵn sàng vì database hiện tại chưa có schema mới. Không có dữ liệu canonical nào bị thay đổi.</p>
        <div class="mt-4 rounded border bg-slate-50 p-3 text-sm">
            <p class="font-semibold">Trên WSL, chạy:</p>
            <code class="mt-2 block">./songchart dev ready</code>
        </div>
    </div>
</x-ui.card>
@else
<x-ui.card class="mt-6"><div class="p-4 flex flex-wrap gap-3 text-sm">
    @foreach(['pending' => 'Chờ duyệt', 'applied' => 'Đã áp dụng', 'rejected' => 'Đã từ chối'] as $key => $label)
        <a class="rounded border px-3 py-2 @if($status === $key) font-semibold @endif" href="{{ route('admin.canonical-admissions.index', ['status' => $key]) }}">{{ $label }}</a>
    @endforeach
</div></x-ui.card>

<section class="mt-6"><x-ui.card><div class="p-4"><h2 class="font-semibold">Quyết định admission</h2><p class="mt-1 text-sm text-slate-500">Evidence chỉ được thay đổi dữ liệu canonical sau quyết định quản trị có audit.</p></div><div class="overflow-x-auto"><table class="min-w-full text-left text-sm"><thead><tr class="border-b"><th class="p-3">Đối tượng</th><th class="p-3">Trường dữ liệu</th><th class="p-3">Nguồn</th><th class="p-3">Giá trị đề xuất</th><th class="p-3">Trạng thái</th><th class="p-3"></th></tr></thead><tbody class="divide-y">@forelse($decisions as $decision)<tr><td class="p-3"><div class="font-semibold">{{ $decision->entity_type->label() }}</div><div class="text-xs text-slate-500">{{ $decision->entity_id }}</div></td><td class="p-3">{{ $decision->field_name }}</td><td class="p-3">{{ $decision->assertion?->source?->name ?? 'Không xác định' }}</td><td class="p-3"><code>{{ json_encode($decision->assertion?->value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) }}</code></td><td class="p-3">{{ ['pending' => 'Chờ duyệt', 'applied' => 'Đã áp dụng', 'rejected' => 'Đã từ chối'][$decision->status->value] ?? $decision->status->value }}</td><td class="p-3"><a class="font-semibold underline" href="{{ route('admin.canonical-admissions.show', $decision) }}">Rà soát</a></td></tr>@empty<tr><td colspan="6" class="p-8 text-center text-slate-500">Không có quyết định phù hợp.</td></tr>@endforelse</tbody></table></div><div class="p-4">{{ $decisions->links() }}</div></x-ui.card></section>

@if($status === 'pending')
<section class="mt-6"><x-ui.card><div class="p-4"><h2 class="font-semibold">Evidence ứng viên chưa vào hàng chờ</h2><p class="mt-1 text-sm text-slate-500">Đưa evidence vào hàng chờ rà soát; thao tác này chưa thay đổi dữ liệu canonical.</p></div><div class="overflow-x-auto"><table class="min-w-full text-left text-sm"><thead><tr class="border-b"><th class="p-3">Đối tượng</th><th class="p-3">Trường dữ liệu</th><th class="p-3">Nguồn</th><th class="p-3">Giá trị đề xuất</th><th class="p-3"></th></tr></thead><tbody class="divide-y">@forelse($unstaged as $assertion)<tr><td class="p-3"><div class="font-semibold">{{ $assertion->entity_type->label() }}</div><div class="text-xs text-slate-500">{{ $assertion->entity_id }}</div></td><td class="p-3">{{ $assertion->field_name }}</td><td class="p-3">{{ $assertion->source?->name ?? 'Không xác định' }}</td><td class="p-3"><code>{{ json_encode($assertion->value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) }}</code></td><td class="p-3"><form method="post" action="{{ route('admin.canonical-admissions.stage', $assertion) }}">@csrf<button class="rounded border px-3 py-2 font-semibold">Đưa vào rà soát</button></form></td></tr>@empty<tr><td colspan="5" class="p-8 text-center text-slate-500">Không có evidence ứng viên chưa được đưa vào hàng chờ.</td></tr>@endforelse</tbody></table></div></x-ui.card></section>
@endif
@endif
@endsection
