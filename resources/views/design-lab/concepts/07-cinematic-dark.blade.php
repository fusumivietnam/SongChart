@extends('design-lab.layout')
@section('body-class', 'bg-black text-white')
@section('content')
@include('design-lab.partials.lab-bar')
<div class="relative min-h-screen overflow-hidden">
    <div class="absolute inset-0 bg-[radial-gradient(circle_at_70%_20%,#7c3aed_0%,transparent_26%),radial-gradient(circle_at_30%_60%,#0e7490_0%,transparent_32%),linear-gradient(#030712,#000)]"></div>
    <div class="absolute inset-0 opacity-25 [background-image:linear-gradient(rgba(255,255,255,.05)_1px,transparent_1px),linear-gradient(90deg,rgba(255,255,255,.05)_1px,transparent_1px)] [background-size:80px_80px]"></div>
    <header class="relative z-10 mx-auto flex max-w-7xl items-center px-6 py-7">
        <span class="text-xl font-black tracking-[.18em]">SONGCHART</span>
        <nav class="ml-auto hidden gap-7 text-sm text-white/60 md:flex"><a>Discover</a><a>Artists</a><a>Library</a></nav>
        <button class="ml-7 rounded-full bg-white/10 px-4 py-2 text-sm backdrop-blur">Search</button>
    </header>
    <main class="relative z-10 mx-auto max-w-7xl px-6 pb-16">
        <section class="grid min-h-[650px] items-center gap-12 lg:grid-cols-2">
            <div>
                <p class="text-xs font-black uppercase tracking-[.35em] text-cyan-300">Immersive artist profile</p>
                <h1 class="mt-6 text-6xl font-black leading-[.88] tracking-[-.07em] md:text-8xl">{{ $sample['featuredArtist']['name'] }}</h1>
                <p class="mt-7 max-w-xl text-lg leading-8 text-white/65">{{ $sample['featuredArtist']['summary'] }}</p>
                <div class="mt-9 flex flex-wrap gap-3">
                    <button class="rounded-full bg-white px-6 py-3 font-bold text-black">Play official video</button>
                    <button class="rounded-full border border-white/20 bg-white/5 px-6 py-3 font-bold backdrop-blur">View metadata</button>
                </div>
            </div>
            <div class="relative mx-auto aspect-[4/5] w-full max-w-md">
                <div class="absolute inset-0 rotate-6 rounded-[3rem] bg-gradient-to-br from-violet-500 to-cyan-300 blur-2xl opacity-60"></div>
                <div class="absolute inset-4 overflow-hidden rounded-[3rem] border border-white/20 bg-gradient-to-br from-fuchsia-600 via-violet-900 to-cyan-600 shadow-2xl">
                    <div class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-black/90 p-8 pt-32"><p class="text-xs font-bold uppercase tracking-widest text-cyan-200">Afterglow District</p><p class="mt-2 text-3xl font-black">Neon Weather</p></div>
                </div>
            </div>
        </section>
        <section class="rounded-[2rem] border border-white/10 bg-white/5 p-5 backdrop-blur-xl">
            <div class="grid gap-2 md:grid-cols-5">
                @foreach($sample['recordings'] as $i => $track)
                    <button class="rounded-2xl p-4 text-left transition hover:bg-white/10">
                        <span class="text-xs text-white/40">0{{ $i+1 }}</span><p class="mt-3 font-bold">{{ $track['title'] }}</p><p class="mt-1 text-xs text-white/50">{{ $track['duration'] }}</p>
                    </button>
                @endforeach
            </div>
        </section>
    </main>
</div>
@endsection
