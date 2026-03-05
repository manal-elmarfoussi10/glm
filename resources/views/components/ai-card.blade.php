@props([
    'available' => false,
])

<div {{ $attributes->merge(['class' => 'rounded-2xl border border-white/10 bg-gradient-to-b from-[#2563EB]/12 to-white/[0.04] backdrop-blur-sm p-6 shadow-lg transition-all duration-300 hover:shadow-xl hover:border-[#2563EB]/20']) }}>
    <div class="flex items-center gap-3">
        <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-[#2563EB]/20 text-[#93C5FD]">
            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" /></svg>
        </div>
        <div>
            <h3 class="text-base font-bold text-white">Assistant IA</h3>
            <p class="text-sm text-slate-400">{{ $available ? 'Disponible' : 'Bientôt disponible' }}</p>
        </div>
    </div>
    <p class="mt-3 text-sm text-slate-300">
        Créer une réservation, générer un contrat ou afficher la rentabilité d’un véhicule en quelques clics.
    </p>
    <div class="mt-4 flex flex-wrap gap-2">
        {{ $slot }}
    </div>
    @if (!$available)
        <p class="mt-4 text-xs text-slate-500">Disponible sur plan Business et supérieurs.</p>
    @endif
</div>
