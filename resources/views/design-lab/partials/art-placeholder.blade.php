<div {{ $attributes->merge(['class' => 'relative overflow-hidden bg-gradient-to-br from-violet-500 via-fuchsia-500 to-orange-300']) }}>
    <div class="absolute inset-0 bg-[radial-gradient(circle_at_25%_20%,rgba(255,255,255,.55),transparent_24%),radial-gradient(circle_at_75%_75%,rgba(15,23,42,.28),transparent_34%)]"></div>
    <div class="absolute inset-x-0 bottom-0 p-4 text-white">
        <span class="text-xs font-bold uppercase tracking-[0.2em]">{{ $label ?? 'Artwork' }}</span>
    </div>
</div>
