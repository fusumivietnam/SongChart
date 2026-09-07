@extends('layouts.admin')
@section('content')
<x-admin.page-header title="Duyệt thay đổi dữ liệu" description="Xem các đề xuất từ nguồn dữ liệu, kiểm tra nội dung và quyết định có cập nhật vào SongChart hay không." />

@if(! $available)
<x-ui.card class="mt-6">
    <div class="p-5">
        <h2 class="font-semibold">Chức năng duyệt chưa sẵn sàng</h2>
        <p class="mt-2 text-sm text-slate-600">Hiện chưa thể tải danh sách đề xuất. Dữ liệu SongChart không bị thay đổi. Nếu tình trạng này tiếp diễn, hãy liên hệ người phụ trách kỹ thuật.</p>
    </div>
</x-ui.card>
@else
<x-ui.card class="mt-6">
    <div class="p-5">
        <h2 class="font-semibold">Cách duyệt một đề xuất</h2>
        <ol class="mt-3 grid gap-3 text-sm md:grid-cols-3">
            <li class="rounded border p-3"><span class="font-semibold">1. Chọn đề xuất</span><p class="mt-1 text-slate-500">Một đề xuất mới chỉ là thông tin cần kiểm tra, chưa làm thay đổi dữ liệu.</p></li>
            <li class="rounded border p-3"><span class="font-semibold">2. Kiểm tra nội dung</span><p class="mt-1 text-slate-500">Xem loại dữ liệu, nguồn cung cấp và giá trị được đề xuất.</p></li>
            <li class="rounded border p-3"><span class="font-semibold">3. Ra quyết định</span><p class="mt-1 text-slate-500">Chấp nhận để cập nhật SongChart hoặc từ chối và ghi rõ lý do.</p></li>
        </ol>
    </div>
</x-ui.card>

<nav class="mt-6" aria-label="Trạng thái duyệt dữ liệu">
    <x-ui.card>
        <div class="grid gap-2 p-4 text-sm sm:grid-cols-3">
            @foreach([
                'pending' => ['label' => 'Cần duyệt', 'description' => 'Đang chờ quyết định'],
                'applied' => ['label' => 'Đã chấp nhận', 'description' => 'Đã cập nhật SongChart'],
                'rejected' => ['label' => 'Đã từ chối', 'description' => 'Không cập nhật dữ liệu'],
            ] as $key => $item)
                <a
                    class="min-h-11 rounded border px-3 py-2 @if($status === $key) font-semibold ring-1 @endif"
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
            <h2 id="admission-decisions-heading" class="font-semibold">{{ $status === 'pending' ? 'Đề xuất cần bạn xem xét' : 'Lịch sử quyết định' }}</h2>
            <p class="mt-1 text-sm text-slate-500">Mở từng đề xuất để xem đầy đủ thông tin trước khi quyết định.</p>
        </div>

        <div class="divide-y md:hidden">
            @forelse($decisions as $decision)
                <article class="p-4">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="font-semibold">{{ $decision->entity_type->label() }}</p>
                            <p class="mt-1 text-sm text-slate-600">{{ str($decision->field_name)->replace('_', ' ')->headline() }}</p>
                        </div>
                        <span class="shrink-0 rounded border px-2 py-1 text-xs font-semibold">{{ ['pending' => 'Cần duyệt', 'applied' => 'Đã chấp nhận', 'rejected' => 'Đã từ chối'][$decision->status->value] ?? $decision->status->value }}</span>
                    </div>
                    <dl class="mt-3 space-y-2 text-sm">
                        <div><dt class="text-xs font-semibold text-slate-500">Giá trị đề xuất</dt><dd class="mt-1 break-words">{{ is_scalar($decision->assertion?->value) || $decision->assertion?->value === null ? (string) ($decision->assertion?->value ?? 'Không có giá trị') : json_encode($decision->assertion?->value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) }}</dd></div>
                        <div><dt class="text-xs font-semibold text-slate-500">Nguồn</dt><dd class="mt-1">{{ $decision->assertion?->source?->name ?? 'Không xác định' }}</dd></div>
                    </dl>
                    <a class="mt-4 flex min-h-11 items-center justify-center rounded border px-3 py-2 font-semibold" href="{{ route('admin.canonical-admissions.show', $decision) }}">Xem chi tiết</a>
                </article>
            @empty
                <p class="p-8 text-center text-sm text-slate-500">Không có đề xuất nào ở trạng thái này.</p>
            @endforelse
        </div>

        <div class="hidden overflow-x-auto md:block">
            <table class="min-w-full text-left text-sm">
                <thead><tr class="border-b"><th class="p-3">Loại dữ liệu</th><th class="p-3">Nội dung đề xuất</th><th class="p-3">Nguồn</th><th class="p-3">Trạng thái</th><th class="p-3"><span class="sr-only">Thao tác</span></th></tr></thead>
                <tbody class="divide-y">
                    @forelse($decisions as $decision)
                        <tr>
                            <td class="p-3"><div class="font-semibold">{{ $decision->entity_type->label() }}</div></td>
                            <td class="p-3">
                                <div class="font-medium">{{ str($decision->field_name)->replace('_', ' ')->headline() }}</div>
                                <div class="mt-1 break-words text-slate-600">{{ is_scalar($decision->assertion?->value) || $decision->assertion?->value === null ? (string) ($decision->assertion?->value ?? 'Không có giá trị') : json_encode($decision->assertion?->value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) }}</div>
                            </td>
                            <td class="p-3">{{ $decision->assertion?->source?->name ?? 'Không xác định' }}</td>
                            <td class="p-3"><span class="inline-flex rounded border px-2 py-1 text-xs font-semibold">{{ ['pending' => 'Cần duyệt', 'applied' => 'Đã chấp nhận', 'rejected' => 'Đã từ chối'][$decision->status->value] ?? $decision->status->value }}</span></td>
                            <td class="p-3"><a class="inline-flex min-h-11 items-center font-semibold underline" href="{{ route('admin.canonical-admissions.show', $decision) }}">Xem chi tiết</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="p-8 text-center text-slate-500">Không có đề xuất nào ở trạng thái này.</td></tr>
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
            <h2 id="unstaged-evidence-heading" class="font-semibold">Đề xuất mới từ các nguồn dữ liệu</h2>
            <p class="mt-1 text-sm text-slate-500">Thêm một đề xuất vào danh sách duyệt để xem xét riêng. Bước này chưa cập nhật dữ liệu SongChart.</p>
        </div>

        <div class="divide-y md:hidden">
            @forelse($unstaged as $assertion)
                <article class="p-4">
                    <p class="font-semibold">{{ $assertion->entity_type->label() }}</p>
                    <p class="mt-1 text-sm text-slate-600">{{ str($assertion->field_name)->replace('_', ' ')->headline() }}</p>
                    <dl class="mt-3 space-y-2 text-sm">
                        <div><dt class="text-xs font-semibold text-slate-500">Giá trị đề xuất</dt><dd class="mt-1 break-words">{{ is_scalar($assertion->value) || $assertion->value === null ? (string) ($assertion->value ?? 'Không có giá trị') : json_encode($assertion->value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) }}</dd></div>
                        <div><dt class="text-xs font-semibold text-slate-500">Nguồn</dt><dd class="mt-1">{{ $assertion->source?->name ?? 'Không xác định' }}</dd></div>
                    </dl>
                    <form class="mt-4" method="post" action="{{ route('admin.canonical-admissions.stage', $assertion) }}">
                        @csrf
                        <button class="min-h-11 w-full rounded border px-3 py-2 font-semibold">Đưa vào danh sách duyệt</button>
                    </form>
                </article>
            @empty
                <p class="p-8 text-center text-sm text-slate-500">Hiện không có đề xuất mới cần đưa vào danh sách duyệt.</p>
            @endforelse
        </div>

        <div class="hidden overflow-x-auto md:block">
            <table class="min-w-full text-left text-sm">
                <thead><tr class="border-b"><th class="p-3">Loại dữ liệu</th><th class="p-3">Nội dung đề xuất</th><th class="p-3">Nguồn</th><th class="p-3"><span class="sr-only">Thao tác</span></th></tr></thead>
                <tbody class="divide-y">
                    @forelse($unstaged as $assertion)
                        <tr>
                            <td class="p-3"><div class="font-semibold">{{ $assertion->entity_type->label() }}</div></td>
                            <td class="p-3">
                                <div class="font-medium">{{ str($assertion->field_name)->replace('_', ' ')->headline() }}</div>
                                <div class="mt-1 break-words text-slate-600">{{ is_scalar($assertion->value) || $assertion->value === null ? (string) ($assertion->value ?? 'Không có giá trị') : json_encode($assertion->value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) }}</div>
                            </td>
                            <td class="p-3">{{ $assertion->source?->name ?? 'Không xác định' }}</td>
                            <td class="p-3">
                                <form method="post" action="{{ route('admin.canonical-admissions.stage', $assertion) }}">
                                    @csrf
                                    <button class="min-h-11 rounded border px-3 py-2 font-semibold">Đưa vào danh sách duyệt</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="p-8 text-center text-slate-500">Hiện không có đề xuất mới cần đưa vào danh sách duyệt.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-ui.card>
</section>
@endif
@endif
@endsection
