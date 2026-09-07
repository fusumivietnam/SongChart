@extends('layouts.admin')
@section('content')
<x-admin.page-header title="Duyệt dữ liệu chuẩn" description="Rà soát evidence theo hàng chờ quản trị trước khi bất kỳ giá trị nào được áp dụng vào dữ liệu canonical." />

@if(! $available)
<x-ui.card class="mt-6">
    <div class="p-5">
        <h2 class="font-semibold">Chưa thể mở hàng chờ duyệt</h2>
        <p class="mt-2 text-sm text-slate-600">Database phát triển hiện tại chưa có schema canonical admission. Không có dữ liệu canonical nào bị thay đổi.</p>
        <div class="mt-4 rounded border bg-slate-50 p-3 text-sm">
            <p class="font-semibold">Khôi phục môi trường phát triển:</p>
            <code class="mt-2 block">./songchart dev ready</code>
        </div>
    </div>
</x-ui.card>
@else
<x-ui.card class="mt-6">
    <div class="p-5">
        <h2 class="font-semibold">Quy trình duyệt</h2>
        <ol class="mt-3 grid gap-3 text-sm md:grid-cols-3">
            <li class="rounded border p-3"><span class="font-semibold">1. Xếp hàng evidence</span><p class="mt-1 text-slate-500">Evidence ứng viên được đưa vào hàng chờ. Chưa thay đổi dữ liệu canonical.</p></li>
            <li class="rounded border p-3"><span class="font-semibold">2. Rà soát quyết định</span><p class="mt-1 text-slate-500">Kiểm tra entity, trường dữ liệu, nguồn và giá trị đề xuất.</p></li>
            <li class="rounded border p-3"><span class="font-semibold">3. Áp dụng hoặc từ chối</span><p class="mt-1 text-slate-500">Quyết định yêu cầu rationale và được ghi audit.</p></li>
        </ol>
    </div>
</x-ui.card>

<nav class="mt-6" aria-label="Trạng thái canonical admission">
    <x-ui.card>
        <div class="flex flex-wrap gap-3 p-4 text-sm">
            @foreach([
                'pending' => ['label' => 'Chờ duyệt', 'description' => 'Cần quyết định'],
                'applied' => ['label' => 'Đã áp dụng', 'description' => 'Đã vào canonical'],
                'rejected' => ['label' => 'Đã từ chối', 'description' => 'Evidence không được áp dụng'],
            ] as $key => $item)
                <a
                    class="rounded border px-3 py-2 @if($status === $key) font-semibold ring-1 @endif"
                    href="{{ route('admin.canonical-admissions.index', ['status' => $key]) }}"
                    @if($status === $key) aria-current="page" @endif
                >
                    <span class="block">{{ $item['label'] }}</span>
                    <span class="block text-xs font-normal text-slate-500">{{ $item['description'] }}</span>
                </a>
            @endforeach
        </div>
    </x-ui.card>
</nav>

<section class="mt-6" aria-labelledby="admission-decisions-heading">
    <x-ui.card>
        <div class="p-4">
            <h2 id="admission-decisions-heading" class="font-semibold">Hàng chờ quyết định</h2>
            <p class="mt-1 text-sm text-slate-500">Mỗi hàng là một quyết định quản trị độc lập. Chỉ màn rà soát chi tiết mới cho phép áp dụng hoặc từ chối.</p>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full text-left text-sm">
                <thead><tr class="border-b"><th class="p-3">Đối tượng</th><th class="p-3">Trường dữ liệu</th><th class="p-3">Nguồn evidence</th><th class="p-3">Giá trị đề xuất</th><th class="p-3">Trạng thái</th><th class="p-3"><span class="sr-only">Thao tác</span></th></tr></thead>
                <tbody class="divide-y">
                    @forelse($decisions as $decision)
                        <tr>
                            <td class="p-3"><div class="font-semibold">{{ $decision->entity_type->label() }}</div><div class="text-xs text-slate-500">{{ $decision->entity_id }}</div></td>
                            <td class="p-3"><code>{{ $decision->field_name }}</code></td>
                            <td class="p-3">{{ $decision->assertion?->source?->name ?? 'Không xác định' }}</td>
                            <td class="p-3"><code class="break-all">{{ json_encode($decision->assertion?->value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) }}</code></td>
                            <td class="p-3"><span class="inline-flex rounded border px-2 py-1 text-xs font-semibold">{{ ['pending' => 'Chờ duyệt', 'applied' => 'Đã áp dụng', 'rejected' => 'Đã từ chối'][$decision->status->value] ?? $decision->status->value }}</span></td>
                            <td class="p-3"><a class="font-semibold underline" href="{{ route('admin.canonical-admissions.show', $decision) }}">Mở rà soát</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="p-8 text-center text-slate-500">Không có quyết định ở trạng thái này.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4">{{ $decisions->links() }}</div>
    </x-ui.card>
</section>

@if($status === 'pending')
<section class="mt-6" aria-labelledby="unstaged-evidence-heading">
    <x-ui.card>
        <div class="p-4">
            <h2 id="unstaged-evidence-heading" class="font-semibold">Evidence chưa xếp hàng</h2>
            <p class="mt-1 text-sm text-slate-500">Đưa evidence vào hàng chờ chỉ tạo quyết định cần rà soát; thao tác này chưa thay đổi dữ liệu canonical.</p>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full text-left text-sm">
                <thead><tr class="border-b"><th class="p-3">Đối tượng</th><th class="p-3">Trường dữ liệu</th><th class="p-3">Nguồn evidence</th><th class="p-3">Giá trị đề xuất</th><th class="p-3"><span class="sr-only">Thao tác</span></th></tr></thead>
                <tbody class="divide-y">
                    @forelse($unstaged as $assertion)
                        <tr>
                            <td class="p-3"><div class="font-semibold">{{ $assertion->entity_type->label() }}</div><div class="text-xs text-slate-500">{{ $assertion->entity_id }}</div></td>
                            <td class="p-3"><code>{{ $assertion->field_name }}</code></td>
                            <td class="p-3">{{ $assertion->source?->name ?? 'Không xác định' }}</td>
                            <td class="p-3"><code class="break-all">{{ json_encode($assertion->value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) }}</code></td>
                            <td class="p-3">
                                <form method="post" action="{{ route('admin.canonical-admissions.stage', $assertion) }}">
                                    @csrf
                                    <button class="rounded border px-3 py-2 font-semibold">Đưa vào hàng chờ</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="p-8 text-center text-slate-500">Không còn evidence ứng viên chưa được xếp hàng.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-ui.card>
</section>
@endif
@endif
@endsection
