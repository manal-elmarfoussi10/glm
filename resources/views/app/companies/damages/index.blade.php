@extends('app.layouts.app')

@section('pageSubtitle')
Dégâts – {{ $company->name }}
@endsection

@section('content')
<div class="space-y-8 glm-fade-in">
    <header class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <a href="{{ route('app.companies.show', $company) }}" class="text-sm font-medium text-slate-400 hover:text-white mb-2 inline-block no-underline">← {{ $company->name }}</a>
            <h1 class="text-2xl font-bold tracking-tight text-white">Dégâts (état des lieux)</h1>
            <p class="mt-1 text-sm text-slate-400">Dégâts constatés au départ ou au retour. Filtres et recherche par réservation, véhicule, client.</p>
        </div>
    </header>

    <form method="get" action="{{ route('app.companies.damages.index', $company) }}" class="glm-card-static flex flex-wrap items-end gap-4 p-5">
        <div class="flex-1 min-w-[180px]">
            <label for="search" class="mb-1 block text-xs font-medium uppercase tracking-wider text-slate-500">Recherche (réf., plaque, client)</label>
            <input type="search" id="search" name="search" value="{{ request('search') }}" placeholder="Réf., plaque, client…" class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm text-white">
        </div>
        <div>
            <label for="type" class="mb-1 block text-xs font-medium uppercase tracking-wider text-slate-500">Type</label>
            <select id="type" name="type" class="rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm text-white">
                <option value="">Tous</option>
                <option value="out" {{ request('type') === 'out' ? 'selected' : '' }}>Départ</option>
                <option value="in" {{ request('type') === 'in' ? 'selected' : '' }}>Retour</option>
            </select>
        </div>
        <div>
            <label for="date_from" class="mb-1 block text-xs font-medium uppercase tracking-wider text-slate-500">Du</label>
            <input type="date" id="date_from" name="date_from" value="{{ request('date_from') }}" class="rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm text-white">
        </div>
        <div>
            <label for="date_to" class="mb-1 block text-xs font-medium uppercase tracking-wider text-slate-500">Au</label>
            <input type="date" id="date_to" name="date_to" value="{{ request('date_to') }}" class="rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm text-white">
        </div>
        <button type="submit" class="glm-btn-primary">Filtrer</button>
        @if (request()->hasAny(['search', 'type', 'date_from', 'date_to']))
            <a href="{{ route('app.companies.damages.index', $company) }}" class="glm-btn-secondary no-underline">Réinitialiser</a>
        @endif
    </form>

    <div class="glm-card-static overflow-hidden p-0">
        <div class="overflow-x-auto">
            <table class="min-w-full border-collapse">
                <thead class="bg-white/5">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase text-slate-400">Réservation</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase text-slate-400">Véhicule · Client</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase text-slate-400">Type</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase text-slate-400">Date</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase text-slate-400">Dégâts</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase text-slate-400">Photos</th>
                        <th class="w-0 px-6 py-4"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/10">
                    @forelse ($damages as $d)
                        @php
                            $res = $d->reservation;
                            $summary = '';
                            if ($d->type === 'out' && !empty($d->damage_checklist)) {
                                $summary = collect($d->damage_checklist)->map(fn ($x) => ($x['area'] ?? '') . ': ' . ($x['description'] ?? ''))->take(2)->implode(' ; ');
                                if (count($d->damage_checklist) > 2) { $summary .= '…'; }
                            } else {
                                $summary = \Str::limit($d->new_damages, 80);
                            }
                        @endphp
                        <tr class="hover:bg-white/5">
                            <td class="px-6 py-4 font-mono text-sm font-semibold text-white">{{ $res->reference }}</td>
                            <td class="px-6 py-4 text-sm text-slate-200">{{ $res->vehicle->plate }} · {{ $res->customer->name }}</td>
                            <td class="px-6 py-4">
                                <span class="rounded-full px-2.5 py-0.5 text-xs font-medium {{ $d->type === 'out' ? 'bg-blue-500/20 text-blue-300' : 'bg-amber-500/20 text-amber-400' }}">{{ $d->type === 'out' ? 'Départ' : 'Retour' }}</span>
                            </td>
                            <td class="px-6 py-4 text-sm text-slate-300">{{ $d->inspected_at ? $d->inspected_at->format('d/m/Y H:i') : '–' }}</td>
                            <td class="px-6 py-4 text-sm text-slate-300 max-w-xs truncate" title="{{ $summary }}">{{ $summary ?: '–' }}</td>
                            <td class="px-6 py-4 text-sm text-slate-300">{{ $d->photos->count() }} photo(s)</td>
                            <td class="px-6 py-4">
                                <a href="{{ route('app.companies.reservations.show', [$company, $res]) }}?tab=inspections" class="glm-btn-secondary text-sm py-1.5 px-3 no-underline">Voir état des lieux</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-slate-400">
                                Aucun dégât enregistré. Les dégâts saisis dans les états des lieux (départ / retour) apparaissent ici.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($damages->hasPages())
            <div class="border-t border-white/10 px-6 py-4">{{ $damages->links() }}</div>
        @endif
    </div>
</div>
@endsection
