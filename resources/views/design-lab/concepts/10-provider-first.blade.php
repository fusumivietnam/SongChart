@extends('design-lab.layout')
@section('body-class', 'bg-[#eef2f6] text-slate-950')
@section('content')
@include('design-lab.partials.lab-bar')
<header class="bg-slate-950 text-white">
    <div class="mx-auto flex max-w-7xl items-center gap-5 px-5 py-5"><span class="text-xl font-black">SongChart</span><div class="ml-auto text-sm text-white/60">Metadata first · Listen elsewhere</div></div>
</header>
<main class="mx-auto max-w-6xl px-5 py-10">
    <section class="overflow-hidden rounded-[2rem] border border-slate-200 bg-white shadow-xl">
        <div class="grid lg:grid-cols-[360px_1fr]">
            <div class="min-h-[360px] bg-gradient-to-br from-blue-700 via-violet-600 to-orange-300 p-7 text-white">
                <div class="flex h-full flex-col justify-between rounded-2xl border border-white/30 p-6"><span class="text-xs font-bold uppercase tracking-[.25em]">Official artwork</span><span class="text-5xl font-black">LV</span></div>
            </div>
            <div class="p-7 md:p-10">
                <p class="text-xs font-bold uppercase tracking-[.2em] text-violet-700">Recording</p>
                <h1 class="mt-3 text-4xl font-black tracking-tight md:text-6xl">Neon Weather</h1>
                <p class="mt-3 text-lg text-slate-600">Luna Vale · 2026 · 3:48</p>
                <p class="mt-6 max-w-2xl leading-7 text-slate-600">Bản thu nổi bật từ album Afterglow District. Provider availability được kiểm tra theo khu vực và thời điểm.</p>
                <div class="mt-8 rounded-2xl bg-slate-950 p-5 text-white">
                    <p class="text-sm font-bold">Chọn nơi nghe</p>
                    <div class="mt-4 grid gap-3 sm:grid-cols-3">
                        @foreach($sample['providers'] as $i => $provider)
                            <button class="rounded-xl {{ ['bg-red-600','bg-pink-600','bg-orange-500'][$i] }} p-4 text-left">
                                <span class="text-sm font-black">{{ $provider['name'] }}</span><span class="mt-5 block text-xs text-white/75">{{ $provider['label'] }}</span><span class="mt-3 block">↗</span>
                            </button>
                        @endforeach
                    </div>
                    <p class="mt-4 text-xs text-white/50">Bạn sẽ được chuyển sang website hoặc ứng dụng của provider.</p>
                </div>
            </div>
        </div>
    </section>
    <section class="mt-8 grid gap-6 lg:grid-cols-[1fr_320px]">
        <div class="rounded-3xl border border-slate-200 bg-white p-7"><h2 class="text-2xl font-black">Thông tin bản thu</h2><dl class="mt-6 grid gap-5 sm:grid-cols-2">@foreach([['Release','Afterglow District'],['Phiên bản','Original studio recording'],['ISRC','SC-26-00001'],['Nguồn nhận diện','MusicBrainz + Editorial']] as [$dt,$dd])<div class="border-t pt-4"><dt class="text-sm text-slate-500">{{ $dt }}</dt><dd class="mt-1 font-bold">{{ $dd }}</dd></div>@endforeach</dl></div>
        <aside class="rounded-3xl border border-slate-200 bg-white p-7"><h2 class="font-black">Phiên bản khác</h2><div class="mt-5 space-y-4">@foreach(array_slice($sample['recordings'],1,3) as $track)<div><p class="font-bold">{{ $track['title'] }}</p><p class="text-sm text-slate-500">{{ $track['artist'] }} · {{ $track['duration'] }}</p></div>@endforeach</div></aside>
    </section>
</main>
@endsection
