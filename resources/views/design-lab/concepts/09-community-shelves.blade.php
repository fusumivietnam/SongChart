@extends('design-lab.layout')
@section('body-class', 'bg-[#f8f7ff] text-slate-950')
@section('content')
@include('design-lab.partials.lab-bar')
<header class="border-b border-violet-100 bg-white">
    <div class="mx-auto flex max-w-7xl items-center px-5 py-5"><span class="text-xl font-black text-violet-800">SongChart</span><nav class="ml-10 hidden gap-6 text-sm font-semibold md:flex"><a>Discover</a><a>Collections</a><a>Curators</a></nav><button class="ml-auto rounded-full bg-violet-700 px-4 py-2 text-sm font-bold text-white">Create collection</button></div>
</header>
<main>
    <section class="bg-gradient-to-b from-violet-100 to-[#f8f7ff]">
        <div class="mx-auto max-w-7xl px-5 py-16">
            <div class="max-w-3xl"><p class="text-sm font-bold text-violet-700">Human-curated discovery</p><h1 class="mt-4 text-5xl font-black tracking-tight md:text-7xl">Âm nhạc có ngữ cảnh, không chỉ là danh sách.</h1><p class="mt-5 text-lg leading-8 text-slate-600">Theo dõi những bộ sưu tập có lý do lựa chọn rõ ràng, nguồn dữ liệu minh bạch và liên kết nghe hợp pháp.</p></div>
        </div>
    </section>
    <section class="mx-auto max-w-7xl px-5 pb-14">
        <div class="-mt-5 grid gap-5 md:grid-cols-3">
            @foreach($sample['collections'] as $i => $collection)
                <article class="overflow-hidden rounded-3xl border border-violet-100 bg-white shadow-sm">
                    <div class="grid aspect-[16/9] grid-cols-3 gap-1 bg-violet-100 p-3">
                        @for($j=0;$j<6;$j++)<div class="rounded-lg bg-gradient-to-br {{ ['from-pink-400 to-orange-300','from-cyan-400 to-indigo-600','from-lime-300 to-emerald-600'][$i] }}"></div>@endfor
                    </div>
                    <div class="p-6"><p class="text-xs font-bold uppercase tracking-wider text-violet-700">Collection · {{ $collection['count'] }} items</p><h2 class="mt-3 text-2xl font-black">{{ $collection['title'] }}</h2><p class="mt-4 text-sm text-slate-500">Curated by {{ $collection['curator'] }}</p><button class="mt-5 font-bold text-violet-700">Open shelf →</button></div>
                </article>
            @endforeach
        </div>
    </section>
    <section class="border-y border-violet-100 bg-white">
        <div class="mx-auto max-w-7xl px-5 py-12"><div class="flex items-end justify-between"><div><p class="text-sm text-violet-700">People behind the context</p><h2 class="mt-2 text-3xl font-black">Curators đáng theo dõi</h2></div><a class="font-bold">View all →</a></div><div class="mt-7 grid gap-4 sm:grid-cols-2 lg:grid-cols-5">@foreach($sample['artists'] as $artist)<article class="rounded-2xl bg-violet-50 p-5"><div class="grid h-14 w-14 place-items-center rounded-full bg-violet-700 font-bold text-white">{{ $artist['initials'] }}</div><h3 class="mt-4 font-bold">{{ $artist['name'] }}</h3><p class="mt-1 text-sm text-slate-500">{{ $artist['meta'] }}</p></article>@endforeach</div></div>
    </section>
</main>
@endsection
