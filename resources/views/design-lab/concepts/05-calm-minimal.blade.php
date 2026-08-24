@extends('design-lab.layout')
@section('body-class', 'bg-[#fbfbfa] text-[#191919]')
@section('content')
@include('design-lab.partials.lab-bar')
<header>
    <div class="mx-auto flex max-w-6xl items-center px-6 py-7">
        <span class="text-lg font-semibold tracking-tight">SongChart</span>
        <nav class="ml-auto flex gap-6 text-sm text-neutral-500"><a>Search</a><a>Explore</a><a>About</a></nav>
    </div>
</header>
<main class="mx-auto max-w-6xl px-6">
    <section class="grid gap-12 border-b border-neutral-200 pb-20 pt-20 md:grid-cols-12 md:pt-28">
        <div class="md:col-span-8">
            <p class="text-sm text-neutral-500">Featured artist</p>
            <h1 class="mt-5 text-6xl font-medium leading-none tracking-[-.055em] md:text-8xl">{{ $sample['featuredArtist']['name'] }}</h1>
        </div>
        <div class="flex flex-col justify-end md:col-span-4">
            <p class="max-w-sm text-lg leading-8 text-neutral-600">{{ $sample['featuredArtist']['summary'] }}</p>
            <a class="mt-7 inline-flex items-center gap-3 font-semibold">Open artist <span>→</span></a>
        </div>
    </section>
    <section class="grid gap-8 border-b border-neutral-200 py-16 md:grid-cols-[220px_1fr]">
        <div><h2 class="font-semibold">Recent releases</h2><p class="mt-2 text-sm text-neutral-500">Selected, not ranked.</p></div>
        <div>
            @foreach(array_slice($sample['releases'], 0, 4) as $i => $release)
                <article class="grid grid-cols-[44px_1fr_auto] items-center gap-4 border-t border-neutral-200 py-5 first:border-0">
                    <span class="text-sm text-neutral-400">0{{ $i + 1 }}</span>
                    <div><h3 class="text-xl font-medium">{{ $release['title'] }}</h3><p class="mt-1 text-sm text-neutral-500">{{ $release['artist'] }}</p></div>
                    <span class="text-sm text-neutral-500">{{ $release['type'] }} · {{ $release['year'] }}</span>
                </article>
            @endforeach
        </div>
    </section>
    <section class="grid gap-8 py-16 md:grid-cols-[220px_1fr]">
        <h2 class="font-semibold">Find anything</h2>
        <div class="border-b-2 border-neutral-900 pb-3"><input class="w-full bg-transparent text-3xl font-medium outline-none" placeholder="Artist, release, recording…"></div>
    </section>
</main>
@endsection
