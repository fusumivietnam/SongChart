@extends('design-lab.layout')
@section('body-class', 'bg-slate-100 text-slate-950')
@section('content')
@include('design-lab.partials.lab-bar')
<div class="grid min-h-screen md:grid-cols-[230px_1fr]">
    <aside class="hidden border-r border-slate-300 bg-white p-5 md:block">
        <div class="text-xl font-black">SongChart</div>
        <nav class="mt-8 space-y-1 text-sm">
            @foreach(['Search','Discover','Artists','Releases','Recordings','Collections','History'] as $i => $item)
                <a class="block rounded-lg px-3 py-2.5 {{ $i===1 ? 'bg-slate-950 font-bold text-white' : 'text-slate-600' }}">{{ $item }}</a>
            @endforeach
        </nav>
        <div class="mt-10 border-t pt-5 text-xs text-slate-500">Fast paths<br>Keyboard-first navigation</div>
    </aside>
    <main>
        <header class="border-b border-slate-300 bg-white p-4">
            <div class="mx-auto flex max-w-7xl items-center gap-4">
                <input class="min-w-0 flex-1 rounded-lg border border-slate-300 bg-slate-50 px-4 py-2.5" placeholder="Search artists, releases, recordings">
                <button class="rounded-lg bg-slate-950 px-4 py-2.5 font-bold text-white">Search</button>
            </div>
        </header>
        <div class="mx-auto max-w-7xl p-5">
            <div class="flex flex-wrap items-end justify-between gap-4">
                <div><p class="text-sm text-slate-500">Discovery workspace</p><h1 class="text-3xl font-black">Khám phá nhanh</h1></div>
                <div class="flex gap-2 text-sm"><button class="rounded-lg border bg-white px-3 py-2">All types</button><button class="rounded-lg border bg-white px-3 py-2">Newest</button></div>
            </div>
            <div class="mt-6 grid gap-5 xl:grid-cols-[1fr_320px]">
                <section class="overflow-hidden rounded-xl border border-slate-300 bg-white">
                    <div class="border-b border-slate-200 px-5 py-4"><h2 class="font-bold">Recent releases</h2></div>
                    <table class="w-full text-left text-sm">
                        <thead class="bg-slate-50 text-xs uppercase text-slate-500"><tr><th class="p-4">Release</th><th class="p-4">Artist</th><th class="p-4">Type</th><th class="p-4">Year</th><th class="p-4"></th></tr></thead>
                        <tbody>
                        @foreach($sample['releases'] as $release)
                            <tr class="border-t border-slate-100"><td class="p-4 font-bold">{{ $release['title'] }}</td><td class="p-4">{{ $release['artist'] }}</td><td class="p-4">{{ $release['type'] }}</td><td class="p-4">{{ $release['year'] }}</td><td class="p-4 text-right">→</td></tr>
                        @endforeach
                        </tbody>
                    </table>
                </section>
                <aside class="space-y-5">
                    <div class="rounded-xl border border-slate-300 bg-white p-5"><h2 class="font-bold">Quick entity lookup</h2><div class="mt-4 space-y-3">@foreach(array_slice($sample['artists'],0,4) as $artist)<div class="flex items-center gap-3"><div class="grid h-9 w-9 place-items-center rounded-lg bg-slate-200 text-xs font-bold">{{ $artist['initials'] }}</div><div><p class="font-semibold">{{ $artist['name'] }}</p><p class="text-xs text-slate-500">{{ $artist['meta'] }}</p></div></div>@endforeach</div></div>
                    <div class="rounded-xl bg-indigo-700 p-5 text-white"><p class="text-xs font-bold uppercase tracking-wider text-indigo-200">Tip</p><p class="mt-3 font-bold">Nhấn / để tìm kiếm từ bất kỳ đâu.</p></div>
                </aside>
            </div>
        </div>
    </main>
</div>
@endsection
