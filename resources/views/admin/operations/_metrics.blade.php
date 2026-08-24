<section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4" aria-label="Chỉ số vận hành">
    @foreach($metrics as $metric)
        <x-ui.card><p class="text-sm text-slate-500">{{ $metric['label'] }}</p><p class="mt-2 text-3xl font-extrabold">{{ number_format($metric['value']) }}</p></x-ui.card>
    @endforeach
</section>
