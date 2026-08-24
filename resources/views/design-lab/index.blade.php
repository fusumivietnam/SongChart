@extends('design-lab.layout')

@section('content')
<div class="min-h-screen bg-[#f3f1ec]">
    <header class="border-b border-slate-300/70 bg-[#f9f7f2]">
        <div class="mx-auto max-w-7xl px-5 py-14">
            <p class="text-sm font-bold uppercase tracking-[0.22em] text-violet-700">SongChart Frontend Design Lab</p>
            <h1 class="mt-4 max-w-4xl text-4xl font-black tracking-tight text-slate-950 md:text-6xl">
                10 hướng giao diện để chốt ngôn ngữ thiết kế
            </h1>
            <p class="mt-5 max-w-3xl text-lg leading-8 text-slate-600">
                Tất cả mẫu dùng cùng một bộ dữ liệu giả. Hãy so sánh khả năng nhận diện,
                tốc độ tìm kiếm, độ rõ CTA provider, khả năng mở rộng trang chi tiết và trải nghiệm mobile.
            </p>
        </div>
    </header>

    <main class="mx-auto max-w-7xl px-5 py-10">
        <div class="grid gap-5 md:grid-cols-2 xl:grid-cols-3">
            @foreach($concepts as $key => $item)
                <a href="{{ route('design-lab.show', $key) }}"
                   class="group flex min-h-64 flex-col justify-between rounded-[1.75rem] border border-slate-300 bg-white p-6 shadow-sm transition hover:-translate-y-1 hover:shadow-xl">
                    <div>
                        <div class="flex items-center justify-between">
                            <span class="font-mono text-sm text-slate-400">{{ str($key)->before('-') }}</span>
                            <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">Interactive Blade</span>
                        </div>
                        <h2 class="mt-8 text-2xl font-black tracking-tight">{{ $item['name'] }}</h2>
                        <p class="mt-3 text-slate-600">{{ $item['direction'] }}</p>
                    </div>
                    <div class="mt-8 flex items-end justify-between gap-5 border-t border-slate-100 pt-5">
                        <p class="text-sm leading-6 text-slate-500">{{ $item['strength'] }}</p>
                        <span class="shrink-0 text-2xl transition group-hover:translate-x-1">→</span>
                    </div>
                </a>
            @endforeach
        </div>

        <section class="mt-12 rounded-[2rem] bg-slate-950 p-7 text-white md:p-10">
            <h2 class="text-2xl font-bold">Cách đánh giá</h2>
            <div class="mt-6 grid gap-6 md:grid-cols-4">
                @foreach([
                    ['5 giây đầu', 'Có hiểu SongChart giúp gì và bắt đầu ở đâu không?'],
                    ['Search → Entity', 'Tìm kiếm và phân biệt đúng artist/release/recording có thuận lợi không?'],
                    ['Entity → Provider', 'CTA mở nơi nghe có rõ nhưng không lấn át metadata không?'],
                    ['Khả năng mở rộng', 'Có thể dùng cùng hệ thống cho trang artist, release và collection không?'],
                ] as [$title, $body])
                    <div>
                        <h3 class="font-semibold">{{ $title }}</h3>
                        <p class="mt-2 text-sm leading-6 text-slate-300">{{ $body }}</p>
                    </div>
                @endforeach
            </div>
        </section>
    </main>
</div>
@endsection
