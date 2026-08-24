<div class="sticky top-0 z-50 border-b border-black/10 bg-white/90 text-slate-900 shadow-sm backdrop-blur">
    <div class="mx-auto flex max-w-[1600px] items-center justify-between gap-4 px-4 py-2 text-sm">
        <div class="flex min-w-0 items-center gap-3">
            <a href="{{ route('design-lab.index') }}" class="font-bold">← Design Lab</a>
            <span class="hidden text-slate-300 sm:inline">/</span>
            <span class="truncate text-slate-600">{{ $concept['name'] }}</span>
        </div>
        <div class="flex items-center gap-2">
            <span class="hidden text-slate-500 md:inline">{{ $concept['direction'] }}</span>
            <a href="{{ route('home') }}" class="rounded-lg border border-slate-200 px-3 py-1.5 font-medium">Core home</a>
        </div>
    </div>
</div>
