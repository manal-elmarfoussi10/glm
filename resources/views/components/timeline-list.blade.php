@props([
    'title',
    'subtitle' => null,
    'viewAllUrl' => null,
    'emptyMessage' => 'Aucun élément',
    'empty' => false,
])

<div {{ $attributes->merge(['class' => 'rounded-2xl border border-white/10 bg-white/[0.06] backdrop-blur-sm p-6 shadow-lg transition-all duration-300 hover:shadow-xl hover:border-white/15']) }}>
    <div class="flex items-center justify-between gap-3">
        <div>
            <h3 class="text-base font-bold text-white">{{ $title }}</h3>
            @if ($subtitle)
                <p class="mt-0.5 text-sm text-slate-400">{{ $subtitle }}</p>
            @endif
        </div>
        @if ($viewAllUrl)
            <a href="{{ $viewAllUrl }}" class="text-sm font-semibold text-[#93C5FD] hover:text-white transition-colors shrink-0">Voir tout →</a>
        @endif
    </div>
    @if ($empty)
        <div class="mt-6 flex flex-col items-center justify-center py-8 text-center">
            <div class="flex h-16 w-16 items-center justify-center rounded-2xl border border-white/10 bg-white/5 text-slate-500">
                <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
            </div>
            <p class="mt-3 text-sm text-slate-500">{{ $emptyMessage }}</p>
        </div>
    @else
        <ul class="mt-4 space-y-2">
            {{ $slot }}
        </ul>
    @endif
</div>
