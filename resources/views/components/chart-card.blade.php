@props([
    'title',
    'subtitle' => null,
])

<div {{ $attributes->merge(['class' => 'rounded-2xl border border-white/10 bg-white/[0.06] backdrop-blur-sm p-6 shadow-lg transition-all duration-300 hover:shadow-xl hover:border-white/15']) }}>
    <div class="mb-4">
        <h3 class="text-base font-bold text-white">{{ $title }}</h3>
        @if ($subtitle)
            <p class="mt-0.5 text-sm text-slate-400">{{ $subtitle }}</p>
        @endif
    </div>
    <div class="relative min-h-[240px] w-full">
        {{ $slot }}
    </div>
</div>
