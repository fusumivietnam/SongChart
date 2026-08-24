@extends('design-lab.layout')
@section('body-class', 'bg-[#fffdf8] text-[#171717]')
@section('content')
@include('design-lab.partials.lab-bar')
<header class="border-b-4 border-black">
    <div class="mx-auto max-w-7xl px-5">
        <div class="flex items-center justify-between py-4 text-xs font-bold uppercase tracking-widest"><span>Monday, August 3, 2026</span><span>Independent music discovery</span></div>
        <div class="border-y border-black py-5 text-center"><span class="font-serif text-5xl font-black tracking-[-.06em] md:text-7xl">SONGCHART</span></div>
        <nav class="flex gap-7 overflow-auto py-4 text-sm font-bold uppercase"><a>Discover</a><a>Profiles</a><a>Releases</a><a>Scenes</a><a>Collections</a><a>Where to listen</a></nav>
    </div>
</header>
<main class="mx-auto max-w-7xl px-5 py-8">
    <section class="grid gap-6 border-b-2 border-black pb-8 lg:grid-cols-[1.2fr_.8fr]">
        <article class="border-r-0 border-black lg:pr-6">
            <div class="aspect-[16/9] bg-gradient-to-br from-red-600 via-amber-300 to-indigo-700"></div>
            <p class="mt-5 text-xs font-black uppercase tracking-[.2em] text-red-700">Cover story</p>
            <h1 class="mt-3 font-serif text-5xl font-black leading-[.95] tracking-tight md:text-7xl">The soft architecture of Luna Vale</h1>
            <p class="mt-5 max-w-3xl font-serif text-xl leading-8 text-neutral-600">{{ $sample['featuredArtist']['summary'] }}</p>
        </article>
        <aside class="grid gap-5">
            @foreach(array_slice($sample['releases'], 0, 3) as $i => $release)
                <article class="grid grid-cols-[120px_1fr] gap-4 border-b border-black pb-5 last:border-0">
                    <div class="aspect-square bg-gradient-to-br {{ ['from-cyan-400 to-blue-700','from-green-400 to-yellow-300','from-violet-600 to-pink-400'][$i] }}"></div>
                    <div><p class="text-xs font-black uppercase tracking-wider">Review</p><h2 class="mt-2 font-serif text-2xl font-bold leading-tight">{{ $release['title'] }}</h2><p class="mt-2 text-sm text-neutral-500">{{ $release['artist'] }} · {{ $release['year'] }}</p></div>
                </article>
            @endforeach
        </aside>
    </section>
    <section class="grid gap-7 py-8 md:grid-cols-3">
        @foreach($sample['collections'] as $collection)
            <article class="border-t-4 border-black pt-4">
                <p class="text-xs font-black uppercase tracking-widest">Listening guide</p>
                <h2 class="mt-4 font-serif text-3xl font-bold leading-tight">{{ $collection['title'] }}</h2>
                <p class="mt-4 text-sm leading-6 text-neutral-600">Một tuyển chọn có chú thích, liên kết nguồn và nhiều lựa chọn nghe hợp pháp.</p>
            </article>
        @endforeach
    </section>
</main>
@endsection
