@extends('app.layouts.app')

@section('content')
<div class="space-y-8">
    <div>
        <h1 class="text-2xl font-bold">Résultats de recherche pour "{{ $query }}"</h1>
        <p class="text-[color:var(--muted)] mt-1">
            {{ $resultsByGroup->flatten(1)->count() }} résultats trouvés.
        </p>
    </div>

    @forelse($resultsByGroup as $type => $group)
        <div class="space-y-4">
            <h2 class="text-sm font-bold uppercase tracking-widest text-[color:var(--muted)]">
                {{ $type === 'company' ? 'Entreprises' : ($type === 'reservation' ? 'Réservations' : ($type === 'customer' ? 'Clients' : 'Véhicules')) }}
            </h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($group as $item)
                    <a href="{{ $item['url'] }}" class="flex items-start gap-4 p-4 rounded-2xl border bg-[color:var(--surface)] border-[color:var(--border)] hover:bg-[color:var(--surface-2)] transition group shadow-[var(--shadow-soft)]">
                        <div class="h-10 w-10 flex items-center justify-center rounded-xl bg-[color:var(--primary)]/10 text-[color:var(--primary)] group-hover:bg-[color:var(--primary)]/20 transition">
                            @if($item['icon'] === 'office-building')
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg>
                            @elseif($item['icon'] === 'calendar')
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                            @elseif($item['icon'] === 'user-group')
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                            @elseif($item['icon'] === 'truck')
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" /></svg>
                            @endif
                        </div>
                        <div class="flex-1 min-w-0">
                            <h3 class="font-bold truncate text-[color:var(--text)]">{{ $item['title'] }}</h3>
                            <p class="text-sm text-[color:var(--muted)] truncate">{{ $item['subtitle'] }}</p>
                        </div>
                        <div class="text-[color:var(--muted)] group-hover:text-[color:var(--primary)] transition">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    @empty
        <div class="flex flex-col items-center justify-center p-12 rounded-3xl border border-dashed border-[color:var(--border)] bg-[color:var(--surface)] text-center">
            <div class="h-16 w-16 flex items-center justify-center rounded-2xl bg-[color:var(--surface-2)] text-[color:var(--muted)] mb-4">
                <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
            </div>
            <h2 class="text-xl font-bold">Aucun résultat</h2>
            <p class="text-[color:var(--muted)] mt-2">Nous n'avons rien trouvé pour "{{ $query }}". Essayez d'autres mots-clés.</p>
        </div>
    @endforelse
</div>
@endsection
