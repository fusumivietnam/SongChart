@extends('design-lab.layout')
@section('body-class', 'bg-white text-slate-950')
@section('content')
@include('design-lab.partials.lab-bar')
<header class="border-b border-slate-200">
    <div class="mx-auto flex max-w-7xl items-center justify-between px-5 py-5">
        <span class="text-xl font-black">SongChart</span>
        <div class="flex items-center gap-5 text-sm font-semibold"><a>Browse</a><a>Collections</a><a>Sign in</a></div>
    </div>
</header>
<main>
    <section class="mx-auto max-w-5xl px-5 pb-14 pt-20 text-center md:pt-28">
        <p class="font-semibold text-violet-700">Music metadata, connected</p>
        <h1 class="mt-4 text-4xl font-black tracking-tight md:text-7xl">Bạn đang tìm bài hát nào?</h1>
        <p class="mx-auto mt-5 max-w-2xl text-lg text-slate-600">Tìm artist, album, bản thu, phiên bản và nơi nghe hợp pháp chỉ trong một lần tìm.</p>
        <div class="mx-auto mt-10 flex max-w-3xl items-center rounded-2xl border-2 border-slate-900 bg-white p-2 shadow-[0_12px_40px_rgba(15,23,42,.12)]">
            <svg class="ml-3 h-6 w-6 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg>
            <input class="min-w-0 flex-1 border-0 bg-transparent px-4 py-3 text-lg outline-none" value="Neon Weather">
            <button class="rounded-xl bg-slate-950 px-6 py-3 font-bold text-white">Tìm kiếm</button>
        </div>
        <div class="mx-auto mt-3 max-w-3xl rounded-2xl border border-slate-200 bg-white p-3 text-left shadow-xl">
            <p class="px-3 py-2 text-xs font-bold uppercase tracking-wider text-slate-400">Gợi ý tốt nhất</p>
            <div class="grid gap-1">
                @foreach(array_slice($sample['recordings'], 0, 3) as $i => $track)
                    <div class="flex items-center gap-4 rounded-xl px-3 py-3 {{ $i === 0 ? 'bg-violet-50' : '' }}">
                        <div class="grid h-11 w-11 place-items-center rounded-lg bg-gradient-to-br from-violet-500 to-cyan-400 font-bold text-white">{{ $i + 1 }}</div>
                        <div class="min-w-0"><p class="font-bold">{{ $track['title'] }}</p><p class="text-sm text-slate-500">Bản thu · {{ $track['artist'] }} · {{ $track['duration'] }}</p></div>
                        <span class="ml-auto rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold">Recording</span>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
    <section class="border-t border-slate-200 bg-slate-50">
        <div class="mx-auto max-w-7xl px-5 py-12">
            <h2 class="text-2xl font-black">Khám phá không cần tìm kiếm</h2>
            <div class="mt-7 grid gap-4 md:grid-cols-3">
                @foreach($sample['collections'] as $collection)
                    <article class="rounded-2xl border border-slate-200 bg-white p-6">
                        <span class="text-xs font-bold uppercase tracking-wider text-violet-700">Editorial collection</span>
                        <h3 class="mt-5 text-xl font-bold">{{ $collection['title'] }}</h3>
                        <p class="mt-2 text-sm text-slate-500">{{ $collection['count'] }} nội dung · {{ $collection['curator'] }}</p>
                    </article>
                @endforeach
            </div>
        </div>
    </section>
</main>
@endsection
