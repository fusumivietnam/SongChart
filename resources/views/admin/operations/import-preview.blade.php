@extends('layouts.admin')

@section('content')
<x-admin.page-header :title="$title" :description="$description" />

<div class="mt-6 grid gap-6 xl:grid-cols-[minmax(0,1fr)_minmax(0,1.15fr)]">
    <x-ui.card>
        <div class="p-5">
            <div class="mb-5 rounded-xl border border-blue-200 bg-blue-50 p-4 text-sm text-blue-900">
                <div class="font-semibold">Chỉ xem trước — chưa nhập dữ liệu</div>
                <p class="mt-1 text-blue-800">SongChart chỉ chuẩn hóa và kiểm tra payload trong request hiện tại. Trang này không tạo import run, không ghi bằng chứng và không thay đổi dữ liệu chuẩn.</p>
            </div>

            <form method="POST" action="{{ route('admin.imports.preview.build') }}" class="space-y-5">
                @csrf

                <div class="grid gap-4 md:grid-cols-2">
                    <label class="block">
                        <span class="mb-1.5 block text-sm font-semibold text-slate-800">Nguồn dữ liệu</span>
                        <select name="provider_slug" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5" required>
                            <option value="musicbrainz" @selected(old('provider_slug', 'musicbrainz') === 'musicbrainz')>MusicBrainz</option>
                        </select>
                        <span class="mt-1 block text-xs text-slate-500">Spotify, Apple Music, YouTube, SoundCloud và Wikidata sẽ xuất hiện khi mapper tương ứng được bật.</span>
                        @error('provider_slug')<span class="mt-1 block text-sm text-red-600">{{ $message }}</span>@enderror
                    </label>

                    <label class="block">
                        <span class="mb-1.5 block text-sm font-semibold text-slate-800">Loại thực thể</span>
                        <select name="entity_type" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5" required>
                            <option value="artist" @selected(old('entity_type', 'recording') === 'artist')>Nghệ sĩ</option>
                            <option value="recording" @selected(old('entity_type', 'recording') === 'recording')>Bản ghi âm</option>
                        </select>
                        @error('entity_type')<span class="mt-1 block text-sm text-red-600">{{ $message }}</span>@enderror
                    </label>
                </div>

                <label class="block">
                    <span class="mb-1.5 block text-sm font-semibold text-slate-800">Mã định danh bên nguồn</span>
                    <input name="external_id" value="{{ old('external_id') }}" class="w-full rounded-xl border border-slate-300 px-3 py-2.5" placeholder="Ví dụ: MusicBrainz ID (MBID)" required>
                    <span class="mt-1 block text-xs text-slate-500">Dùng đúng ID của entity mà payload mô tả để preview identity evidence chính xác.</span>
                    @error('external_id')<span class="mt-1 block text-sm text-red-600">{{ $message }}</span>@enderror
                </label>

                <label class="block">
                    <span class="mb-1.5 block text-sm font-semibold text-slate-800">Dữ liệu JSON từ provider</span>
                    <textarea name="payload_json" rows="16" class="w-full rounded-xl border border-slate-300 px-3 py-2.5 font-mono text-sm" placeholder='{"title":"Example Song","length":181000,"isrcs":["USAAA2600001"]}' required>{{ old('payload_json') }}</textarea>
                    <span class="mt-1 block text-xs text-slate-500">Tối đa 1 MiB. Secret/API key không nên xuất hiện trong payload preview.</span>
                    @error('payload_json')<span class="mt-1 block text-sm text-red-600">{{ $message }}</span>@enderror
                </label>

                <div class="flex flex-wrap items-center gap-3">
                    <button type="submit" class="rounded-xl bg-indigo-600 px-5 py-2.5 font-semibold text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">Xem trước dữ liệu</button>
                    <a href="{{ route('admin.imports.index') }}" class="rounded-xl border border-slate-300 px-4 py-2.5 font-semibold text-slate-700">Quay lại lịch sử nhập</a>
                </div>
            </form>
        </div>
    </x-ui.card>

    <div>
        @if($preview)
            @php($normalized = $preview->normalized)
            <x-ui.card>
                <div class="p-5">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div>
                            <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">Kết quả xem trước</div>
                            <h2 class="mt-1 text-xl font-bold text-slate-900">{{ $preview->valid ? 'Có thể tiếp tục xử lý' : 'Cần sửa dữ liệu trước' }}</h2>
                            <p class="mt-1 text-sm text-slate-600">Đây là cấu trúc SongChart nhận được sau bước mapper + validation.</p>
                        </div>
                        <x-ui.badge :variant="$preview->valid ? 'success' : 'warning'">{{ $preview->valid ? 'Hợp lệ' : 'Cần kiểm tra' }}</x-ui.badge>
                    </div>

                    <div class="mt-5 grid grid-cols-2 gap-3 sm:grid-cols-4">
                        @foreach([
                            'fields' => 'Thuộc tính',
                            'identifiers' => 'Định danh',
                            'relationships' => 'Quan hệ',
                            'media_assets' => 'Media',
                            'destinations' => 'Điểm đến',
                            'availability' => 'Khả dụng',
                            'classifications' => 'Phân loại',
                            'metrics' => 'Chỉ số',
                        ] as $key => $label)
                            <div class="rounded-xl border border-slate-200 bg-slate-50 p-3">
                                <div class="text-2xl font-bold text-slate-900">{{ $preview->counts[$key] ?? 0 }}</div>
                                <div class="mt-1 text-xs font-medium text-slate-600">{{ $label }}</div>
                            </div>
                        @endforeach
                    </div>

                    @if($preview->issues !== [])
                        <div class="mt-5 rounded-xl border border-amber-200 bg-amber-50 p-4">
                            <h3 class="font-semibold text-amber-900">Điểm cần kiểm tra</h3>
                            <ul class="mt-2 space-y-2 text-sm text-amber-900">
                                @foreach($preview->issues as $issue)
                                    <li><strong>{{ $issue['path'] }}</strong>: {{ $issue['message'] }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="mt-6 space-y-5">
                        <section>
                            <h3 class="text-sm font-bold text-slate-900">Thuộc tính đã chuẩn hóa</h3>
                            <div class="mt-2 overflow-x-auto rounded-xl border border-slate-200">
                                <table class="min-w-full text-left text-sm">
                                    <thead class="bg-slate-50 text-slate-600"><tr><th class="p-3">Thuộc tính</th><th class="p-3">Tình trạng</th><th class="p-3">Giá trị</th></tr></thead>
                                    <tbody class="divide-y divide-slate-100">
                                        @foreach($normalized['fields'] as $name => $field)
                                            <tr>
                                                <td class="p-3 font-semibold text-slate-800">{{ $name }}</td>
                                                <td class="p-3 text-slate-600">{{ $field['presence'] }}</td>
                                                <td class="p-3 text-slate-700">{{ array_key_exists('value', $field) ? (is_scalar($field['value']) ? (string) $field['value'] : json_encode($field['value'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)) : '—' }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </section>

                        <section>
                            <h3 class="text-sm font-bold text-slate-900">Định danh và quan hệ</h3>
                            <div class="mt-2 grid gap-3 md:grid-cols-2">
                                <div class="rounded-xl border border-slate-200 p-4">
                                    <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">Định danh</div>
                                    @forelse($normalized['identifiers'] as $identifier)
                                        <div class="mt-2 text-sm"><span class="font-semibold">{{ $identifier['namespace'] }}</span><div class="break-all text-slate-600">{{ $identifier['value'] }}</div></div>
                                    @empty
                                        <p class="mt-2 text-sm text-slate-500">Không có định danh bổ sung.</p>
                                    @endforelse
                                </div>
                                <div class="rounded-xl border border-slate-200 p-4">
                                    <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">Quan hệ</div>
                                    @forelse($normalized['relationships'] as $relationship)
                                        <div class="mt-2 text-sm"><span class="font-semibold">{{ $relationship['type'] }}</span><div class="break-all text-slate-600">{{ $relationship['target_entity_type'] }} · {{ $relationship['target_external_id'] }}</div></div>
                                    @empty
                                        <p class="mt-2 text-sm text-slate-500">Không có quan hệ trong payload này.</p>
                                    @endforelse
                                </div>
                            </div>
                        </section>

                        <details class="rounded-xl border border-slate-200 p-4">
                            <summary class="cursor-pointer font-semibold text-slate-800">Xem dữ liệu chuẩn hóa đầy đủ</summary>
                            <pre class="mt-3 max-h-[32rem] overflow-auto whitespace-pre-wrap break-words rounded-lg bg-slate-950 p-4 text-xs text-slate-100">{{ json_encode($normalized, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) }}</pre>
                        </details>
                    </div>
                </div>
            </x-ui.card>
        @else
            <x-ui.card>
                <div class="p-8 text-center">
                    <div class="text-lg font-bold text-slate-900">Chưa có dữ liệu xem trước</div>
                    <p class="mx-auto mt-2 max-w-md text-sm text-slate-600">Chọn nguồn, loại thực thể, nhập ID và payload JSON. SongChart sẽ hiển thị cách dữ liệu được chuẩn hóa trước khi bạn quyết định bước tiếp theo.</p>
                </div>
            </x-ui.card>
        @endif
    </div>
</div>
@endsection
