@extends('design-lab.layout')
@section('body-class', 'bg-[#f4f7fb] text-slate-950')
@section('content')
@include('design-lab.partials.lab-bar')
<header class="border-b border-slate-200 bg-white">
    <div class="mx-auto flex max-w-7xl items-center gap-8 px-5 py-4">
        <span class="font-mono text-xl font-black">songchart.graph</span>
        <div class="ml-auto flex gap-5 text-sm font-semibold text-slate-600"><a>Entities</a><a>Relations</a><a>Sources</a></div>
    </div>
</header>
<main class="mx-auto max-w-7xl px-5 py-10">
    <div class="grid gap-6 lg:grid-cols-[1fr_340px]">
        <section class="rounded-3xl border border-slate-200 bg-white p-6 md:p-9">
            <div class="flex flex-wrap items-start gap-6">
                <div class="grid h-32 w-32 place-items-center rounded-full bg-gradient-to-br from-indigo-500 to-cyan-300 text-3xl font-black text-white">LV</div>
                <div class="min-w-0 flex-1">
                    <div class="flex flex-wrap items-center gap-3"><span class="rounded-full bg-indigo-50 px-3 py-1 text-xs font-bold text-indigo-700">ARTIST</span><span class="text-sm text-slate-500">Verified identity</span></div>
                    <h1 class="mt-4 text-4xl font-black">{{ $sample['featuredArtist']['name'] }}</h1>
                    <p class="mt-3 max-w-2xl text-slate-600">{{ $sample['featuredArtist']['summary'] }}</p>
                    <div class="mt-5 flex flex-wrap gap-2 text-sm">
                        <span class="rounded-lg bg-slate-100 px-3 py-2">Bangkok</span><span class="rounded-lg bg-slate-100 px-3 py-2">Dream pop</span><span class="rounded-lg bg-slate-100 px-3 py-2">Active 2018–</span>
                    </div>
                </div>
            </div>
            <div class="relative mt-12 min-h-[460px] overflow-hidden rounded-3xl border border-slate-200 bg-[radial-gradient(#cbd5e1_1px,transparent_1px)] [background-size:22px_22px]">
                <svg class="absolute inset-0 h-full w-full" viewBox="0 0 900 460">
                    <g stroke="#94a3b8" stroke-width="2">
                        <line x1="450" y1="220" x2="170" y2="100"/><line x1="450" y1="220" x2="720" y2="90"/>
                        <line x1="450" y1="220" x2="180" y2="360"/><line x1="450" y1="220" x2="720" y2="350"/>
                    </g>
                </svg>
                <div class="absolute left-1/2 top-1/2 grid h-32 w-32 -translate-x-1/2 -translate-y-1/2 place-items-center rounded-full bg-indigo-600 text-center font-black text-white shadow-xl">Luna<br>Vale</div>
                <div class="absolute left-[8%] top-[12%] rounded-2xl border bg-white p-4 shadow-lg"><b>Afterglow District</b><p class="text-xs text-slate-500">Release · 2026</p></div>
                <div class="absolute right-[8%] top-[10%] rounded-2xl border bg-white p-4 shadow-lg"><b>Neon Weather</b><p class="text-xs text-slate-500">Recording</p></div>
                <div class="absolute bottom-[10%] left-[9%] rounded-2xl border bg-white p-4 shadow-lg"><b>Mira Sol</b><p class="text-xs text-slate-500">Related artist</p></div>
                <div class="absolute bottom-[10%] right-[8%] rounded-2xl border bg-white p-4 shadow-lg"><b>Glass Harbour</b><p class="text-xs text-slate-500">Collaborator</p></div>
            </div>
        </section>
        <aside class="space-y-5">
            <div class="rounded-3xl bg-slate-950 p-6 text-white">
                <p class="text-xs font-bold uppercase tracking-wider text-cyan-300">Primary action</p>
                <h2 class="mt-3 text-xl font-bold">Chọn nơi nghe</h2>
                <div class="mt-5 space-y-2">
                    @foreach($sample['providers'] as $provider)
                        <button class="flex w-full items-center justify-between rounded-xl bg-white/10 px-4 py-3 text-left"><span>{{ $provider['name'] }}</span><span>↗</span></button>
                    @endforeach
                </div>
            </div>
            <div class="rounded-3xl border border-slate-200 bg-white p-6">
                <h2 class="font-bold">Data confidence</h2>
                <div class="mt-4 space-y-4 text-sm">
                    <div><div class="flex justify-between"><span>Identity</span><b>High</b></div><div class="mt-2 h-2 rounded-full bg-slate-100"><div class="h-2 w-[92%] rounded-full bg-emerald-500"></div></div></div>
                    <div><div class="flex justify-between"><span>Relationships</span><b>Medium</b></div><div class="mt-2 h-2 rounded-full bg-slate-100"><div class="h-2 w-[68%] rounded-full bg-amber-400"></div></div></div>
                </div>
            </div>
        </aside>
    </div>
</main>
@endsection
