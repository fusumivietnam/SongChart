@extends('design-lab.layout')
@section('body-class', 'bg-[#f5f1e8] text-[#17211c]')
@section('content')
@include('design-lab.partials.lab-bar')
<header class="border-b border-[#17211c]/15 bg-[#f5f1e8]">
    <div class="mx-auto flex max-w-7xl items-center gap-8 px-5 py-5">
        <a class="font-serif text-2xl font-black tracking-tight">SongChart</a>
        <nav class="hidden gap-6 text-sm font-semibold md:flex">
            <a>Khám phá</a><a>Nghệ sĩ</a><a>Phát hành</a><a>Bộ sưu tập</a>
        </nav>
        <div class="ml-auto flex items-center gap-3">
            <button class="rounded-full border border-[#17211c]/25 px-4 py-2 text-sm">Đăng nhập</button>
        </div>
    </div>
</header>
<main>
    <section class="mx-auto grid max-w-7xl gap-10 px-5 py-12 lg:grid-cols-[1.08fr_.92fr] lg:py-20">
        <div class="flex flex-col justify-center">
            <p class="text-xs font-black uppercase tracking-[.28em] text-emerald-800">Featured artist · August 2026</p>
            <h1 class="mt-5 max-w-3xl font-serif text-5xl font-black leading-[.98] tracking-tight md:text-7xl">{{ $sample['featuredArtist']['name'] }}</h1>
            <p class="mt-6 max-w-2xl font-serif text-xl leading-8 text-[#425148]">{{ $sample['featuredArtist']['summary'] }}</p>
            <p class="mt-4 text-sm text-[#66746b]">{{ $sample['featuredArtist']['meta'] }}</p>
            <div class="mt-8 flex flex-wrap gap-3">
                <button class="rounded-full bg-[#17211c] px-6 py-3 font-bold text-white">Khám phá nghệ sĩ</button>
                <button class="rounded-full border border-[#17211c]/25 px-6 py-3 font-bold">Chọn nơi nghe</button>
            </div>
        </div>
        <div class="grid grid-cols-5 gap-3">
            <div class="col-span-4 row-span-2 min-h-[430px] rounded-[2rem] bg-gradient-to-br from-emerald-300 via-amber-100 to-rose-300 p-7">
                <div class="flex h-full flex-col justify-between rounded-[1.5rem] border border-white/60 p-6">
                    <span class="text-xs font-bold uppercase tracking-[.25em]">Artist portrait</span>
                    <span class="font-serif text-4xl font-black">LV</span>
                </div>
            </div>
            <div class="rounded-full bg-emerald-900"></div>
            <div class="rounded-full bg-amber-400"></div>
        </div>
    </section>

    <section class="border-y border-[#17211c]/15 bg-[#ebe4d5]">
        <div class="mx-auto max-w-7xl px-5 py-12">
            <div class="flex items-end justify-between">
                <div><p class="text-xs font-bold uppercase tracking-[.25em]">New & notable</p><h2 class="mt-2 font-serif text-3xl font-black">Phát hành đáng chú ý</h2></div>
                <a class="text-sm font-bold">Xem tất cả →</a>
            </div>
            <div class="mt-8 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                @foreach(array_slice($sample['releases'], 0, 3) as $release)
                    <article class="group">
                        <div class="aspect-square rounded-[1.5rem] bg-gradient-to-br from-stone-800 via-emerald-700 to-amber-300 p-5 transition group-hover:-translate-y-1">
                            <div class="flex h-full items-end rounded-xl border border-white/30 p-5 text-white">
                                <span class="font-serif text-2xl font-bold">{{ $release['title'] }}</span>
                            </div>
                        </div>
                        <h3 class="mt-4 font-serif text-xl font-bold">{{ $release['title'] }}</h3>
                        <p class="mt-1 text-sm text-[#66746b]">{{ $release['artist'] }} · {{ $release['type'] }} · {{ $release['year'] }}</p>
                    </article>
                @endforeach
            </div>
        </div>
    </section>
</main>
@endsection
