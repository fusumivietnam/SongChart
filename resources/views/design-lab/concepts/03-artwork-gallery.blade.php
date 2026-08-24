@extends('design-lab.layout')
@section('body-class', 'bg-[#111214] text-white')
@section('content')
@include('design-lab.partials.lab-bar')
<header class="absolute inset-x-0 z-20">
    <div class="mx-auto flex max-w-[1500px] items-center px-6 py-6">
        <span class="text-2xl font-black">SC/</span>
        <nav class="ml-10 hidden gap-7 text-sm text-white/70 md:flex"><a>Explore</a><a>Artists</a><a>Releases</a></nav>
        <button class="ml-auto rounded-full border border-white/30 px-4 py-2 text-sm">Search</button>
    </div>
</header>
<main>
    <section class="relative min-h-[760px] overflow-hidden">
        <div class="absolute inset-0 bg-[linear-gradient(115deg,#171923_0%,#171923_35%,#4338ca_68%,#f59e0b_100%)]"></div>
        <div class="absolute -right-24 top-28 h-[560px] w-[560px] rotate-12 rounded-[5rem] border border-white/30 bg-gradient-to-br from-fuchsia-500 via-orange-300 to-cyan-300 shadow-2xl"></div>
        <div class="absolute right-[18%] top-[24%] h-36 w-36 rounded-full border-[24px] border-white/20"></div>
        <div class="relative mx-auto flex min-h-[760px] max-w-[1500px] items-end px-6 pb-16">
            <div class="max-w-3xl">
                <p class="text-sm font-bold uppercase tracking-[.3em] text-orange-200">Selected for you</p>
                <h1 class="mt-5 text-6xl font-black leading-[.9] tracking-[-.06em] md:text-9xl">{{ $sample['featuredArtist']['name'] }}</h1>
                <p class="mt-6 max-w-xl text-lg leading-8 text-white/75">{{ $sample['featuredArtist']['summary'] }}</p>
                <div class="mt-8 flex gap-3">
                    <button class="rounded-full bg-white px-6 py-3 font-bold text-slate-950">View artist</button>
                    <button class="rounded-full border border-white/30 px-6 py-3 font-bold">Listen elsewhere</button>
                </div>
            </div>
        </div>
    </section>
    <section class="px-5 py-12 md:px-10">
        <div class="mx-auto max-w-[1500px]">
            <div class="flex items-end justify-between"><h2 class="text-3xl font-black">Visual releases</h2><a class="text-white/60">View archive →</a></div>
            <div class="mt-8 grid grid-cols-2 gap-3 md:grid-cols-3 lg:grid-cols-6">
                @foreach($sample['releases'] as $i => $release)
                    <article class="{{ $i === 0 ? 'col-span-2 row-span-2' : '' }}">
                        <div class="aspect-square rounded-2xl bg-gradient-to-br {{ ['from-pink-500 to-orange-300','from-cyan-400 to-blue-700','from-yellow-300 to-red-600','from-green-400 to-violet-700','from-slate-300 to-slate-800','from-purple-400 to-rose-300'][$i] }} p-4">
                            <div class="flex h-full items-end border border-white/30 p-4"><span class="font-bold">{{ $release['title'] }}</span></div>
                        </div>
                        <p class="mt-3 font-semibold">{{ $release['artist'] }}</p>
                    </article>
                @endforeach
            </div>
        </div>
    </section>
</main>
@endsection
