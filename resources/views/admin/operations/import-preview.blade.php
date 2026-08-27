@extends('layouts.admin')
@php($activeAdminNav = 'import-workbench')

@section('content')
<x-admin.page-header :title="$title" :description="$description" />

<div class="mt-6 grid gap-6 xl:grid-cols-[minmax(0,1fr)_minmax(20rem,.72fr)]">
    <x-ui.card>
        <div class="p-5 md:p-6">
            <div class="rounded-2xl border border-indigo-100 bg-indigo-50 p-5">
                <div class="text-sm font-bold text-indigo-950">Bạn chỉ cần nhập điều bạn đang biết</div>
                <p class="mt-1 text-sm leading-6 text-indigo-800">Tên ca sĩ, nhóm nhạc hoặc tên bài hát là đủ để bắt đầu. SongChart sẽ tìm định danh bên nguồn và tải dữ liệu chi tiết; bạn không cần biết MBID hay JSON.</p>
            </div>

            <form method="GET" action="{{ route('admin.imports.search') }}" class="mt-6 space-y-5">
                <fieldset>
                    <legend class="text-sm font-bold text-slate-900">Bạn đang có thông tin gì?</legend>
                    <div class="mt-3 grid gap-3 sm:grid-cols-3">
                        @foreach([
                            'artist' => ['Nghệ sĩ / nhóm nhạc', 'Ví dụ: Adele, Da LAB'],
                            'recording' => ['Tên bài hát', 'Ví dụ: Hello, Nước mắt em lau bằng tình yêu mới'],
                            'lyrics' => ['Đoạn lời bài hát', 'SongChart sẽ dùng khi có nguồn lời được duyệt'],
                        ] as $value => $copy)
                            <label class="cursor-pointer rounded-xl border border-slate-200 p-4 has-[:checked]:border-indigo-500 has-[:checked]:bg-indigo-50">
                                <input type="radio" name="intent" value="{{ $value }}" class="mr-2" @checked(old('intent', $intent ?? 'artist') === $value)>
                                <span class="font-semibold text-slate-900">{{ $copy[0] }}</span>
                                <span class="mt-1 block text-xs leading-5 text-slate-500">{{ $copy[1] }}</span>
                            </label>
                        @endforeach
                    </div>
                    @error('intent')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                </fieldset>

                <label class="block">
                    <span class="mb-1.5 block text-sm font-bold text-slate-900">Tìm kiếm</span>
                    <div class="flex flex-col gap-3 sm:flex-row">
                        <input name="query" value="{{ old('query', $query ?? '') }}" class="min-w-0 flex-1 rounded-xl border border-slate-300 px-4 py-3" placeholder="Nhập tên nghệ sĩ, nhóm nhạc hoặc bài hát…" required autofocus>
                        <button class="rounded-xl bg-indigo-600 px-6 py-3 font-semibold text-white hover:bg-indigo-700">Tìm thông tin</button>
                    </div>
                    @error('query')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                </label>
            </form>

            @if(($searchNotice ?? null) !== null)
                <div class="mt-5 rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm leading-6 text-amber-900">{{ $searchNotice }}</div>
            @endif

            @if(($results ?? []) !== [])
                <section class="mt-7" aria-labelledby="import-search-results">
                    <div class="flex items-end justify-between gap-4">
                        <div>
                            <h2 id="import-search-results" class="text-lg font-bold text-slate-900">Kết quả phù hợp</h2>
                            <p class="mt-1 text-sm text-slate-500">Chọn đúng người hoặc bài hát. SongChart sẽ tự tải bản ghi đầy đủ và dựng kế hoạch nhập trước khi có thay đổi.</p>
                        </div>
                        <span class="text-sm font-semibold text-slate-500">{{ count($results) }} kết quả</span>
                    </div>
                    <div class="mt-4 space-y-3">
                        @foreach($results as $result)
                            <form method="POST" action="{{ route('admin.imports.select') }}" class="rounded-xl border border-slate-200 p-4 transition hover:border-indigo-300 hover:bg-slate-50">
                                @csrf
                                <input type="hidden" name="provider_slug" value="musicbrainz">
                                <input type="hidden" name="entity_type" value="{{ ($intent ?? 'artist') === 'artist' ? 'artist' : 'recording' }}">
                                <input type="hidden" name="external_id" value="{{ $result['id'] }}">
                                <div class="flex flex-col gap-4 sm:flex-row sm:items-center">
                                    <div class="min-w-0 flex-1">
                                        @if(($intent ?? 'artist') === 'artist')
                                            <div class="font-bold text-slate-900">{{ $result['name'] }}</div>
                                            <div class="mt-1 text-sm text-slate-500">{{ collect([$result['type'] ?: null, $result['country'] ?: null, $result['disambiguation'] ?: null])->filter()->implode(' · ') ?: 'Nghệ sĩ / nhóm nhạc trên MusicBrainz' }}</div>
                                        @else
                                            <div class="font-bold text-slate-900">{{ $result['title'] }}</div>
                                            <div class="mt-1 text-sm text-slate-500">{{ collect([$result['artist_credit'] ?: null, $result['disambiguation'] ?: null])->filter()->implode(' · ') ?: 'Bản ghi âm trên MusicBrainz' }}</div>
                                        @endif
                                    </div>
                                    <button class="shrink-0 rounded-xl border border-indigo-200 bg-white px-4 py-2.5 font-semibold text-indigo-700 hover:bg-indigo-50">Chọn và xem kế hoạch</button>
                                </div>
                            </form>
                        @endforeach
                    </div>
                </section>
            @endif
        </div>
    </x-ui.card>

    <div class="space-y-6">
        <x-ui.card>
            <div class="p-5">
                <h2 class="font-bold text-slate-900">Luồng nhập an toàn</h2>
                <ol class="mt-4 space-y-3 text-sm text-slate-600">
                    <li><strong class="text-slate-900">1. Tìm</strong> — bằng thông tin quen thuộc.</li>
                    <li><strong class="text-slate-900">2. Chọn</strong> — SongChart tải dữ liệu từ nguồn.</li>
                    <li><strong class="text-slate-900">3. Kiểm tra</strong> — xem kế hoạch, định danh, quan hệ và cảnh báo.</li>
                    <li><strong class="text-slate-900">4. Xác nhận</strong> — mới tạo tác vụ nhập có kiểm soát.</li>
                </ol>
                <p class="mt-4 rounded-lg bg-slate-50 p-3 text-xs leading-5 text-slate-500">Không có bước nào ghi trực tiếp dữ liệu chuẩn. Canonical admission và identity resolution vẫn giữ nguyên.</p>
            </div>
        </x-ui.card>

        <details class="rounded-2xl border border-slate-200 bg-white p-5">
            <summary class="cursor-pointer font-semibold text-slate-800">Chế độ nâng cao: ID + JSON</summary>
            <p class="mt-2 text-sm text-slate-500">Dành cho chẩn đoán hoặc payload đã có sẵn. Người vận hành thông thường không cần dùng phần này.</p>
            <form method="POST" action="{{ route('admin.imports.preview.build') }}" class="mt-4 space-y-3">
                @csrf
                <select name="entity_type" class="w-full rounded-xl border border-slate-300 px-3 py-2.5">
                    <option value="artist">Nghệ sĩ</option>
                    <option value="recording">Bản ghi âm</option>
                </select>
                <input type="hidden" name="provider_slug" value="musicbrainz">
                <input name="external_id" class="w-full rounded-xl border border-slate-300 px-3 py-2.5" placeholder="MusicBrainz ID (MBID)">
                <textarea name="payload_json" rows="8" class="w-full rounded-xl border border-slate-300 px-3 py-2.5 font-mono text-xs" placeholder="Dán JSON provider…"></textarea>
                <button class="w-full rounded-xl border border-slate-300 px-4 py-2.5 font-semibold text-slate-700">Xem trước payload kỹ thuật</button>
            </form>
        </details>

        <a href="{{ route('admin.imports.index') }}" class="block text-center text-sm font-semibold text-indigo-700 underline">Xem lịch sử tác vụ</a>
    </div>
</div>
@endsection
